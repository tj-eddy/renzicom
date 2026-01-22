<?php

namespace App\Controller;

use App\Entity\Warehouse;
use App\Form\WarehouseType;
use App\Repository\WarehouseRepository;
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
        private readonly TranslatorInterface $translator
    )
    {
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
        $warehouse = new Warehouse();
        $form = $this->createForm(WarehouseType::class, $warehouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($warehouse);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('messages.success.created'));

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
        $form = $this->createForm(WarehouseType::class, $warehouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('messages.success.updated'));

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
        if ($this->isCsrfTokenValid('delete' . $warehouse->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($warehouse);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('messages.success.deleted'));
        }

        return $this->redirectToRoute('app_warehouse_index');
    }
}
