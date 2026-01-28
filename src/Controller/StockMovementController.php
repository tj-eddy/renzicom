<?php

namespace App\Controller;

use App\Repository\StockMovementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stock-movement')]
class StockMovementController extends AbstractController
{
    #[Route('/', name: 'app_stock_movement_index', methods: ['GET'])]
    public function index(StockMovementRepository $stockMovementRepository): Response
    {
        return $this->render('stock_movement/index.html.twig', [
            'movements' => $stockMovementRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }
}
