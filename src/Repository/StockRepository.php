<?php

// src/Repository/StockRepository.php
namespace App\Repository;

use App\Entity\Stock;
use App\Entity\Distribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    public function restoreFromCancelledDistribution(Distribution $distribution): void
    {
        $product = $distribution->getProduct();
        $quantity = $distribution->getQuantity();

        if (!$product || $quantity <= 0) {
            return;
        }

        $entityManager = $this->getEntityManager();

        // Rechercher le stock existant pour ce produit
        $stock = $this->findOneBy(['product' => $product]);

        if ($stock) {
            // Réajuster la quantité
            $stock->setQuantity($stock->getQuantity() + $quantity);
            $stock->setNote(
                sprintf(
                    'Réajustement suite à annulation de distribution #%d (ancienne note: %s)',
                    $distribution->getId(),
                    $stock->getNote()
                )
            );
        } else {
            // Créer un nouveau stock si aucun n'existe
            $stock = new Stock();
            $stock->setProduct($product);
            $stock->setQuantity($quantity);
            $stock->setNote(
                sprintf('Stock créé suite à annulation de distribution #%d', $distribution->getId())
            );
            $stock->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($stock);
        }

        $entityManager->flush();
    }
}
