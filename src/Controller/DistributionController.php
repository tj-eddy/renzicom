<?php

namespace App\Controller;

use App\Entity\Distribution;
use App\Entity\Product;
use App\Entity\Stock;
use App\Form\DistributionType;
use App\Repository\RackRepository;
use App\Repository\StockRepository;
use App\Repository\WarehouseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/distribution')]
class DistributionController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }
    #[Route('/', name: 'app_distribution_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $distributions = $entityManager
            ->getRepository(Distribution::class)
            ->findBy([], ['id' => 'DESC']);

        return $this->render('distribution/index.html.twig', [
            'distributions' => $distributions,
        ]);
    }

    #[Route('/new', name: 'app_distribution_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        WarehouseRepository $warehouseRepository
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied'));
            return $this->redirectToRoute('app_distribution_index');
        }
        $distribution = new Distribution();
        $form = $this->createForm(DistributionType::class, $distribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données des produits sélectionnés
            $productsData = json_decode($request->request->get('products_data'), true);

            if (empty($productsData)) {
                $this->addFlash('error', 'Veuillez sélectionner au moins un produit');
                return $this->redirectToRoute('app_distribution_new');
            }

            // Créer une distribution pour chaque produit
            foreach ($productsData as $data) {
                $product = $entityManager->getRepository(Product::class)->find($data['product_id']);
                $stock = $entityManager->getRepository(Stock::class)->find($data['stock_id']);

                if (!$product || !$stock) {
                    continue;
                }

                // Vérifier si la quantité demandée est disponible
                if ($data['quantity'] > $stock->getQuantity()) {
                    $this->addFlash('error', sprintf(
                        'Quantité insuffisante pour %s (disponible: %d, demandé: %d)',
                        $product->getName(),
                        $stock->getQuantity(),
                        $data['quantity']
                    ));
                    continue;
                }

                // Créer la distribution
                $newDistribution = new Distribution();
                $newDistribution->setProduct($product);
                $newDistribution->setQuantity($data['quantity']);
                $newDistribution->setUser($distribution->getUser());
                $newDistribution->setDestination($distribution->getDestination());
                $newDistribution->setStatus('en_preparation');
                $newDistribution->setCreateAt(new \DateTimeImmutable());

                // Mettre à jour le stock
                $stock->setQuantity($stock->getQuantity() - $data['quantity']);

                $entityManager->persist($newDistribution);
                $entityManager->persist($stock);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Distribution créée avec succès');
            return $this->redirectToRoute('app_distribution_index');
        }

        return $this->render('distribution/new.html.twig', [
            'distribution' => $distribution,
            'form' => $form,
            'warehouses' => $warehouseRepository->findBy([], ['id' => 'DESC']),
            'button_label' => 'create',
        ]);
    }

    /**
     * Route AJAX pour récupérer les racks d'un entrepôt
     */
    #[Route('/ajax/racks/{warehouseId}', name: 'app_distribution_ajax_racks', methods: ['GET'])]
    public function getRacksByWarehouse(
        int $warehouseId,
        RackRepository $rackRepository
    ): JsonResponse {
        $racks = $rackRepository->findBy(['warehouse' => $warehouseId]);

        $data = [];
        foreach ($racks as $rack) {
            $data[] = [
                'id' => $rack->getId(),
                'name' => $rack->getName(),
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * Route AJAX pour récupérer les produits avec stock d'un rack
     */
    #[Route('/ajax/stocks/{rackId}', name: 'app_distribution_ajax_stocks', methods: ['GET'])]
    public function getStocksByRack(
        int $rackId,
        StockRepository $stockRepository
    ): JsonResponse {
        $stocks = $stockRepository->createQueryBuilder('s')
            ->where('s.rack = :rackId')
            ->andWhere('s.quantity > 0')
            ->setParameter('rackId', $rackId)
            ->leftJoin('s.product', 'p')
            ->addSelect('p')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($stocks as $stock) {
            $product = $stock->getProduct();
            $data[] = [
                'stock_id' => $stock->getId(),
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
                'product_year' => $product->getYearEdition(),
                'product_language' => $product->getLanguage(),
                'available_quantity' => $stock->getQuantity(),
                'note' => $stock->getNote(),
            ];
        }

        return new JsonResponse($data);
    }
    /**
     * Changer le statut d'une distribution
     */
    #[Route('/{id}/change-status/{status}', name: 'app_distribution_change_status', methods: ['POST'])]
    public function changeStatus(
        StockRepository $stockRepository,
        Distribution $distribution,
        string $status,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérifier le token CSRF

        if ($this->isGranted('ROLE_STATISTICS')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied.change_status'));
            return $this->redirectToRoute('app_distribution_index');
        }
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('change-status' . $distribution->getId(), $token)) {
            $this->addFlash('error', $this->translator->trans('distribution.error.csrf_token_invalid'));
            return $this->redirectToRoute('app_distribution_index');
        }

        // Vérifier que le statut est valide
        if (!in_array($status, [
            Distribution::STATUS_PREPARING,
            Distribution::STATUS_IN_PROGRESS,
            Distribution::STATUS_DELIVERED,
            Distribution::STATUS_CANCELLED
        ], true)) {
            $this->addFlash('error', $this->translator->trans('distribution.error.invalid_status'));
            return $this->redirectToRoute('app_distribution_index');
        }

        $oldStatus = $distribution->getStatus();



        // CAS 1 : Passage vers CANCELLED - Restaurer le stock
        if ($status === Distribution::STATUS_CANCELLED && $oldStatus !== Distribution::STATUS_CANCELLED) {
            $stockRepository->restoreFromCancelledDistribution($distribution);
            $this->addFlash('success', $this->translator->trans('distribution.status.cancelled_stock_restored', [
                '{quantity}' => $distribution->getQuantity()
            ]));
        }

        // CAS 2 : Réactivation depuis CANCELLED - Déduire le stock
        elseif ($oldStatus === Distribution::STATUS_CANCELLED && $status !== Distribution::STATUS_CANCELLED) {
            if (!$stockRepository->hasEnoughStock($distribution)) {
                $this->addFlash('error', $this->translator->trans('distribution.error.insufficient_stock'));
                return $this->redirectToRoute('app_distribution_index');
            }

            if ($stockRepository->deductFromReactivatedDistribution($distribution)) {
                $this->addFlash('success', $this->translator->trans('distribution.status.reactivated_stock_deducted', [
                    '{quantity}' => $distribution->getQuantity()
                ]));
            } else {
                $this->addFlash('error', $this->translator->trans('distribution.error.stock_deduction_failed'));
                return $this->redirectToRoute('app_distribution_index');
            }
        }

        // CAS 3 : Changement de statut normal
        else {
            $this->addFlash('success', $this->translator->trans('distribution.status.updated_success'));
        }

        $distribution->setStatus($status);
        $entityManager->flush();
        return $this->redirectToRoute('app_distribution_index');
    }

    /**
     * Supprimer une distribution
     */
    #[Route('/{id}', name: 'app_distribution_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Distribution $distribution,
        EntityManagerInterface $entityManager,
        StockRepository $stockRepository
    ): Response {
        if (!$this->isGranted(['ROLE_ADMIN','ROLE_DRIVER'])) {
            $this->addFlash('error', $this->translator->trans('error.access_denied.change_status'));
            return $this->redirectToRoute('app_distribution_index');
        }

        if ($this->isCsrfTokenValid('delete' . $distribution->getId(), $request->request->get('_token'))) {
            $shouldRestoreStock = false;
            $quantity = $distribution->getQuantity();

            // Si la distribution n'est pas annulée, on doit remettre le stock
            if ($distribution->getStatus() !== Distribution::STATUS_CANCELLED) {
                $stockRepository->restoreFromCancelledDistribution($distribution);
                $shouldRestoreStock = true;
            }
            $entityManager->remove($distribution);
            $entityManager->flush();
            if ($shouldRestoreStock) {
                $this->addFlash('success', $this->translator->trans('distribution.delete.success_with_stock', [
                    '{quantity}' => $quantity
                ]));
            } else {
                $this->addFlash('success', $this->translator->trans('distribution.delete.success'));
            }
        } else {
            $this->addFlash('error', $this->translator->trans('distribution.delete.error_csrf'));
        }

        return $this->redirectToRoute('app_distribution_index');
    }


}
