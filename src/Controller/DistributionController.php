<?php

namespace App\Controller;

use App\Entity\Distribution;
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
        $form = $this->createForm(DistributionType::class, $distribution);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Si le statut est "delivered", mettre à jour completedAt
                if (Distribution::STATUS_DELIVERED === $distribution->getStatus()) {
                    $distribution->setCompletedAt(new \DateTimeImmutable());
                }

                $this->entityManager->persist($distribution);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('distribution.created'));

                return $this->redirectToRoute('app_distribution_index');
            } catch (\Exception $e) {
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

        $form = $this->createForm(DistributionType::class, $distribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {


                // Si le statut passe à "delivered", gérer le retour du stock non distribué
                if (Distribution::STATUS_DELIVERED === $distribution->getStatus()) {
                    $distribution->setCompletedAt(new \DateTimeImmutable());
                }

                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('distribution.updated'));

                return $this->redirectToRoute('app_distribution_index');
            } catch (\Exception $e) {
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

            // Marquer la distribution comme terminée
            $distribution->setStatus(Distribution::STATUS_DELIVERED);
            $distribution->setCompletedAt(new \DateTimeImmutable());

            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('distribution.messages.completed'));
        } catch (\Exception $e) {
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
                $this->entityManager->remove($distribution);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('distribution.deleted'));
            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        } else {
            $this->addFlash('error', $this->translator->trans('exception.invalid_token'));
        }

        return $this->redirectToRoute('app_distribution_index');
    }
}
