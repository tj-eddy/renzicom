<?php

namespace App\Controller;

use App\Entity\Warehouse;
use App\Form\WarehouseType;
use App\Repository\WarehouseRepository;
use App\Security\PermissionChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/warehouse')]
class WarehouseController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly PermissionChecker $permissionChecker,
    ) {
    }

    #[Route('/', name: 'app_warehouse_index', methods: ['GET'])]
    public function index(WarehouseRepository $warehouseRepository): Response
    {
        return $this->render('warehouse/index.html.twig', [
            'warehouses' => $warehouseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_warehouse_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // Vérification de permission: seuls les admins peuvent créer des entrepôts
        if (!$this->permissionChecker->canCreateProductOrWarehouse()) {
            $this->addFlash('error', $this->translator->trans('access.denied.create_warehouse'));
            return $this->redirectToRoute('app_warehouse_index');
        }

        $warehouse = new Warehouse();
        $form = $this->createForm(WarehouseType::class, $warehouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($warehouse);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('warehouse.created'));

            return $this->redirectToRoute('app_warehouse_index');
        }

        return $this->render('warehouse/new.html.twig', [
            'warehouse' => $warehouse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_warehouse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Warehouse $warehouse): Response
    {
        // Vérification de permission: seuls les admins peuvent modifier des entrepôts
        if (!$this->permissionChecker->canCreateProductOrWarehouse()) {
            $this->addFlash('error', $this->translator->trans('access.denied.create_warehouse'));
            return $this->redirectToRoute('app_warehouse_index');
        }

        $form = $this->createForm(WarehouseType::class, $warehouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('warehouse.updated'));

            return $this->redirectToRoute('app_warehouse_index');
        }

        return $this->render('warehouse/edit.html.twig', [
            'warehouse' => $warehouse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_warehouse_delete', methods: ['POST'])]
    public function delete(Request $request, Warehouse $warehouse): Response
    {
        // Vérification de permission: seuls les admins peuvent supprimer
        if (!$this->permissionChecker->canDelete()) {
            $this->addFlash('error', $this->translator->trans('access.denied.delete_any'));
            return $this->redirectToRoute('app_warehouse_index');
        }

        if ($this->isCsrfTokenValid('delete' . $warehouse->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($warehouse);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('warehouse.deleted'));
        } else {
            $this->addFlash('error', $this->translator->trans('exception.invalid_token'));
        }

        return $this->redirectToRoute('app_warehouse_index');
    }

    /**
     * Récupérer les produits disponibles dans un entrepôt avec leurs quantités
     */
    #[Route('/{id}/products', name: 'get_warehouse_products', methods: ['GET'])]
    public function getWarehouseProducts(int $id, WarehouseRepository $warehouseRepository): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $warehouse = $warehouseRepository->find($id);

        if (!$warehouse) {
            return $this->json(['error' => $this->translator->trans('warehouse.not_found')], 404);
        }

        $products = [];

        foreach ($warehouse->getStocks() as $stock) {
            if ($stock->getQuantity() > 0 && $stock->getProduct()) {
                $product = $stock->getProduct();
                $products[] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'quantity' => $stock->getQuantity(),
                    'image' => $product->getImage(),
                    'label' => sprintf('%s (Stock: %d)', $product->getName(), $stock->getQuantity()),
                ];
            }
        }

        return $this->json($products);
    }

}
