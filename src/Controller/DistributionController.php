<?php

namespace App\Controller;

use App\Entity\Distribution;
use App\Form\DistributionType;
use App\Repository\DistributionRepository;
use App\Service\StockManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/distribution')]
class DistributionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private StockManager $stockManager,
    ) {
    }

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
        $distribution = new Distribution();
        $form = $this->createForm(DistributionType::class, $distribution);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Vérifier et déduire le stock de l'entrepôt
                $this->stockManager->deductStockForDistribution($request->request->all());

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
        $oldStatus = $distribution->getStatus();
        $oldQuantity = $distribution->getQuantity();
        $oldProduct = $distribution->getProduct();

        $form = $this->createForm(DistributionType::class, $distribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Si le produit ou la quantité a changé, gérer le stock
                if (
                    $oldProduct !== $distribution->getProduct()
                    || $oldQuantity !== $distribution->getQuantity()
                ) {
                    // Restaurer l'ancien stock
                    $oldDistribution = new Distribution();
                    $oldDistribution->setProduct($oldProduct);
                    $oldDistribution->setQuantity($oldQuantity);
                    $this->stockManager->restoreStockForDistribution($oldDistribution);

                    // Déduire le nouveau stock
                    $this->stockManager->deductStockForDistribution($request->request->all());
                }

                // Si le statut passe à "delivered", gérer le retour du stock non distribué
                if (
                    Distribution::STATUS_DELIVERED === $distribution->getStatus()
                    && Distribution::STATUS_DELIVERED !== $oldStatus
                ) {
                    // Retourner les magazines non distribués à l'entrepôt
                    $remainingQuantity = $distribution->getQuantityRemaining();

                    if ($remainingQuantity > 0) {
                        $this->stockManager->returnRemainingStock($distribution);
                        $this->addFlash('info', $this->translator->trans('distribution.messages.returned', ['%count%' => $remainingQuantity]));
                    }

                    $distribution->setCompletedAt(new \DateTimeImmutable());
                }

                // Si on revient en arrière depuis delivered, réinitialiser completedAt
                if (
                    Distribution::STATUS_DELIVERED !== $distribution->getStatus()
                    && Distribution::STATUS_DELIVERED === $oldStatus
                ) {
                    $distribution->setCompletedAt(null);
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
            // Calculer la quantité restante
            $remainingQuantity = $distribution->getQuantityRemaining();

            if ($remainingQuantity > 0) {
                // Retourner les magazines non distribués à l'entrepôt
                $this->stockManager->returnRemainingStock($distribution);

                $this->addFlash('success', $this->translator->trans('distribution.messages.returned', ['%count%' => $remainingQuantity]));
            } else {
                $this->addFlash('success', $this->translator->trans('distribution.messages.all_distributed'));
            }

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

    #[Route('/{id}', name: 'app_distribution_delete', methods: ['POST'])]
    public function delete(Request $request, Distribution $distribution): Response
    {
        if ($this->isCsrfTokenValid('delete' . $distribution->getId(), $request->request->get('_token'))) {
            try {
                // Restaurer le stock dans l'entrepôt
                $this->stockManager->restoreStockForDistribution($distribution);

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



    /**
     * Vérifier la quantité disponible pour un produit dans un entrepôt
     */
    #[Route('/check-stock', name: 'api_check_stock', methods: ['POST'])]
    public function checkStock(Request $request, WarehouseRepository $warehouseRepository): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $warehouseId = $data['warehouse_id'] ?? null;
        $productId = $data['product_id'] ?? null;
        $requestedQuantity = $data['quantity'] ?? 0;

        if (!$warehouseId || !$productId) {
            return $this->json(['error' => 'Paramètres manquants'], 400);
        }

        $warehouse = $warehouseRepository->find($warehouseId);

        if (!$warehouse) {
            return $this->json(['error' => 'Entrepôt non trouvé'], 404);
        }

        $availableQuantity = 0;

        foreach ($warehouse->getStocks() as $stock) {
            if ($stock->getProduct() && $stock->getProduct()->getId() === $productId) {
                $availableQuantity = $stock->getQuantity();
                break;
            }
        }

        return $this->json([
            'available' => $availableQuantity,
            'requested' => $requestedQuantity,
            'sufficient' => $availableQuantity >= $requestedQuantity,
            'message' => $availableQuantity >= $requestedQuantity
                ? sprintf('Stock suffisant (%d disponibles)', $availableQuantity)
                : sprintf('Stock insuffisant (%d disponibles, %d demandés)', $availableQuantity, $requestedQuantity),
        ]);
    }
}
