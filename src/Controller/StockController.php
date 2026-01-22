<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Form\StockType;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/stock')]
final class StockController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Récupère le stock disponible pour un produit dans un rack
     *
     * @param Request $request
     * @param StockRepository $stockRepository
     * @return JsonResponse
     */
    #[Route('/check-stock', name: 'app_stock_check', methods: ['GET'])]
    public function checkStock(Request $request, StockRepository $stockRepository): JsonResponse
    {
        $productId = $request->query->get('product_id');
        $rackId = $request->query->get('rack_id');

        if (!$productId || !$rackId) {
            return $this->json(['quantity' => 0, 'exists' => false]);
        }

        $stock = $stockRepository->findOneBy([
            'product' => $productId,
            'rack' => $rackId,
        ]);

        if ($stock) {
            return $this->json([
                'quantity' => $stock->getQuantity(),
                'exists' => true,
                'stock_id' => $stock->getId(),
            ]);
        }

        return $this->json(['quantity' => 0, 'exists' => false]);
    }
    #[Route(name: 'app_stock_index', methods: ['GET'])]
    public function index(StockRepository $stockRepository): Response
    {
        return $this->render('stock/index.html.twig', [
            'stocks' => $stockRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_stock_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        StockRepository $stockRepository
    ): Response {

        if ($this->isGranted('ROLE_STATISTICS')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied'));
            return $this->redirectToRoute('app_stock_index');
        }
        $stock = new Stock();
        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si un stock existe déjà pour ce produit et ce rack
            $existingStock = $stockRepository->findOneBy([
                'product' => $stock->getProduct(),
                'rack' => $stock->getRack(),
            ]);

            if ($existingStock) {
                // Mettre à jour le stock existant
                $newQuantity = $existingStock->getQuantity() + $stock->getQuantity();
                $existingStock->setQuantity($newQuantity);

                // Mettre à jour la note si fournie
                if ($stock->getNote()) {
                    $existingStock->setNote($stock->getNote());
                }

                $entityManager->flush();

                $this->addFlash('success', sprintf(
                    'Stock mis à jour ! Nouvelle quantité : %d',
                    $newQuantity
                ));
            } else {
                // Créer un nouveau stock
                $entityManager->persist($stock);
                $entityManager->flush();

                $this->addFlash('success', 'Stock créé avec succès !');
            }

            return $this->redirectToRoute('app_stock_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stock/new.html.twig', [
            'stock' => $stock,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_stock_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Stock $stock, EntityManagerInterface $entityManager): Response
    {
        if ($this->isGranted('ROLE_STATISTICS')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied'));
            return $this->redirectToRoute('app_stock_index');
        }
        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_stock_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stock/edit.html.twig', [
            'stock' => $stock,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stock_delete', methods: ['POST'])]
    public function delete(Request $request, Stock $stock, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied'));
            return $this->redirectToRoute('app_stock_index');
        }
        if ($this->isCsrfTokenValid('delete'.$stock->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($stock);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_stock_index', [], Response::HTTP_SEE_OTHER);
    }
}
