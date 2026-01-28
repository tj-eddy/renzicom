<?php

namespace App\Controller;

use App\Entity\Distribution;
use App\Entity\StockMovement;
use App\Service\StockManager;
use App\Form\DistributionType;
use App\Repository\DistributionRepository;
use App\Repository\WarehouseRepository;
use App\Security\PermissionChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/distribution')]
class DistributionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private StockManager $stockManager,
        private PermissionChecker $permissionChecker,
    ) {}

    #[Route('/', name: 'app_distribution_index', methods: ['GET'])]
    public function index(DistributionRepository $distributionRepository): Response
    {
        return $this->render('distribution/index.html.twig', [
            'distributions' => $distributionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_distribution_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // Vérification de permission: seuls les admins et livreurs peuvent créer des distributions
        if (!$this->permissionChecker->canCreateDistribution()) {
            $this->addFlash('error', $this->translator->trans('access.denied.create_distribution'));
            return $this->redirectToRoute('app_distribution_index');
        }

        $distribution = new Distribution();
        $isAdmin = $this->permissionChecker->canCreateProductOrWarehouse(); // Si peut créer produits = admin

        // Si c'est un livreur, définir automatiquement l'utilisateur connecté
        if (!$isAdmin) {
            $distribution->setUser($this->getUser());
        }

        $form = $this->createForm(DistributionType::class, $distribution, [
            'is_admin' => $isAdmin,
            'current_user' => $this->getUser(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->beginTransaction();

                // Si le statut est "delivered", mettre à jour completedAt
                if (Distribution::STATUS_DELIVERED === $distribution->getStatus()) {
                    $distribution->setCompletedAt(new \DateTimeImmutable());
                }

                // Déduire le stock de l'entrepôt pour la distribution
                $this->stockManager->deductWarehouseStock(
                    $distribution->getProduct(),
                    $distribution->getQuantity(),
                    null, // Choisir l'entrepôt automatiquement ou passer un entrepôt spécifique si ajouté au formulaire
                    StockMovement::TYPE_OUT,
                    'Distribution #' . $distribution->getId() . ' créée'
                );

                $this->entityManager->persist($distribution);
                $this->entityManager->flush();
                $this->entityManager->commit();

                $this->addFlash('success', $this->translator->trans('distribution.created'));

                return $this->redirectToRoute('app_distribution_index');
            } catch (\Exception $e) {
                $this->entityManager->rollback();
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('distribution/new.html.twig', [
            'distribution' => $distribution,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_distribution_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Distribution $distribution): Response
    {
        // Vérification de permission: seuls les admins et livreurs peuvent modifier des distributions
        if (!$this->permissionChecker->canEditDistribution()) {
            $this->addFlash('error', $this->translator->trans('access.denied.edit_distribution'));
            return $this->redirectToRoute('app_distribution_index');
        }

        $oldQuantity = $distribution->getQuantity();
        $isAdmin = $this->permissionChecker->canCreateProductOrWarehouse(); // Si peut créer produits = admin

        $form = $this->createForm(DistributionType::class, $distribution, [
            'is_admin' => $isAdmin,
            'current_user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->beginTransaction();

                $newQuantity = $distribution->getQuantity();
                $diff = $newQuantity - $oldQuantity;

                if ($diff > 0) {
                    $this->stockManager->deductWarehouseStock($distribution->getProduct(), $diff, null, StockMovement::TYPE_OUT, 'Ajustement distribution #' . $distribution->getId());
                } elseif ($diff < 0) {
                    $this->stockManager->restoreWarehouseStock($distribution->getProduct(), abs($diff), null, StockMovement::TYPE_RETURN, 'Ajustement distribution #' . $distribution->getId());
                }

                // Si le statut passe à "delivered", gérer le retour du stock non distribué
                if (Distribution::STATUS_DELIVERED === $distribution->getStatus()) {
                    $distribution->setCompletedAt(new \DateTimeImmutable());
                }

                $this->entityManager->flush();
                $this->entityManager->commit();

                $this->addFlash('success', $this->translator->trans('distribution.updated'));

                return $this->redirectToRoute('app_distribution_index');
            } catch (\Exception $e) {
                $this->entityManager->rollback();
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('distribution/edit.html.twig', [
            'distribution' => $distribution,
            'form' => $form,
        ]);
    }

    /**
     * Terminer une distribution et retourner le stock restant.
     */
    #[Route('/{id}/complete', name: 'app_distribution_complete', methods: ['POST'])]
    public function complete(Request $request, Distribution $distribution): Response
    {
        // Vérification de permission: seuls les admins et livreurs peuvent compléter des distributions
        if (!$this->permissionChecker->canEditDistribution()) {
            $this->addFlash('error', $this->translator->trans('access.denied.edit_distribution'));
            return $this->redirectToRoute('app_distribution_index');
        }

        // Vérification CSRF
        if (!$this->isCsrfTokenValid('complete' . $distribution->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', $this->translator->trans('exception.invalid_token'));

            return $this->redirectToRoute('app_distribution_index');
        }

        // Vérifier que la distribution n'est pas déjà terminée
        if (Distribution::STATUS_DELIVERED === $distribution->getStatus()) {
            $this->addFlash('warning', $this->translator->trans('distribution.messages.already_completed'));

            return $this->redirectToRoute('app_distribution_index');
        }

        try {
            $this->entityManager->beginTransaction();

            // Marquer la distribution comme terminée
            $distribution->setStatus(Distribution::STATUS_DELIVERED);
            $distribution->setCompletedAt(new \DateTimeImmutable());

            // Retourner le stock restant dans l'entrepôt
            $remaining = $distribution->getQuantity();
            if ($remaining > 0) {
                $this->stockManager->restoreWarehouseStock(
                    $distribution->getProduct(),
                    $remaining,
                    null,
                    StockMovement::TYPE_RETURN,
                    'Retour du stock invendu de la distribution #' . $distribution->getId()
                );
                $distribution->setQuantity(0);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->addFlash('success', $this->translator->trans('distribution.messages.completed'));
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $this->addFlash('danger', '❌ Erreur: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_distribution_index');
    }

    /**
     * Supprimer une distribution.
     */
    #[Route('/{id}', name: 'app_distribution_delete', methods: ['POST'])]
    public function delete(Request $request, Distribution $distribution): Response
    {
        // Vérification de permission: seuls les admins peuvent supprimer
        if (!$this->permissionChecker->canDelete()) {
            $this->addFlash('error', $this->translator->trans('access.denied.delete_any'));
            return $this->redirectToRoute('app_distribution_index');
        }

        if ($this->isCsrfTokenValid('delete' . $distribution->getId(), $request->request->get('_token'))) {
            try {
                $this->entityManager->beginTransaction();

                // Restaurer TOUT le stock au moment de la suppression
                if ($distribution->getQuantity() > 0) {
                    $this->stockManager->restoreWarehouseStock(
                        $distribution->getProduct(),
                        $distribution->getQuantity(),
                        null,
                        StockMovement::TYPE_RETURN,
                        'Annulation distribution #' . $distribution->getId()
                    );
                }

                $this->entityManager->remove($distribution);
                $this->entityManager->flush();
                $this->entityManager->commit();

                $this->addFlash('success', $this->translator->trans('distribution.deleted'));
            } catch (\Exception $e) {
                $this->entityManager->rollback();
                $this->addFlash('danger', $e->getMessage());
            }
        } else {
            $this->addFlash('error', $this->translator->trans('exception.invalid_token'));
        }

        return $this->redirectToRoute('app_distribution_index');
    }
}
