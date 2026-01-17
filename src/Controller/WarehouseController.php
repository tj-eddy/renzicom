<?php

namespace App\Controller;

use App\Entity\Warehouse;
use App\Entity\WarehouseImage;
use App\Form\WarehouseType;
use App\Repository\WarehouseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/warehouse')]
final class WarehouseController extends AbstractController
{
    #[Route(name: 'app_warehouse_index', methods: ['GET'])]
    public function index(WarehouseRepository $warehouseRepository): Response
    {
        return $this->render('warehouse/index.html.twig', [
            'warehouses' => $warehouseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_warehouse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $warehouse = new Warehouse();
        $form = $this->createForm(WarehouseType::class, $warehouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion des images uploadées
            $imageFiles = $form->get('images')->getData();

            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $warehouseImage = new WarehouseImage();
                    $warehouseImage->setImageFile($imageFile);
                    $warehouseImage->setWarehouse($warehouse);
                    $entityManager->persist($warehouseImage);
                }
            }

            $entityManager->persist($warehouse);
            $entityManager->flush();

            $this->addFlash('success', 'Entrepôt créé avec succès');

            return $this->redirectToRoute('app_warehouse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('warehouse/new.html.twig', [
            'warehouse' => $warehouse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_warehouse_show', methods: ['GET'])]
    public function show(Warehouse $warehouse): Response
    {
        return $this->render('warehouse/show.html.twig', [
            'warehouse' => $warehouse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_warehouse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Warehouse $warehouse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WarehouseType::class, $warehouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion des nouvelles images uploadées
            $imageFiles = $form->get('images')->getData();

            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $warehouseImage = new WarehouseImage();
                    $warehouseImage->setImageFile($imageFile);
                    $warehouseImage->setWarehouse($warehouse);
                    $entityManager->persist($warehouseImage);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Entrepôt modifié avec succès');

            return $this->redirectToRoute('app_warehouse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('warehouse/edit.html.twig', [
            'warehouse' => $warehouse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_warehouse_delete', methods: ['POST'])]
    public function delete(Request $request, Warehouse $warehouse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$warehouse->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($warehouse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_warehouse_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Delete a specific warehouse image
     */
    #[Route('/image/{id}/delete', name: 'app_warehouse_image_delete', methods: ['POST'])]
    public function deleteImage(Request $request, WarehouseImage $image, EntityManagerInterface $entityManager): Response
    {
        $warehouseId = $image->getWarehouse()->getId();

        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $entityManager->remove($image);
            $entityManager->flush();

            $this->addFlash('success', 'Image supprimée avec succès');
        }

        return $this->redirectToRoute('app_warehouse_edit', ['id' => $warehouseId], Response::HTTP_SEE_OTHER);
    }
}
