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

    /**
     * Restaure le stock lors de l'annulation d'une distribution
     */
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
            // Réajuster la quantité (AJOUTER au stock)
            $stock->setQuantity($stock->getQuantity() + $quantity);
            $stock->setNote(
                sprintf(
                    'Réajustement suite à annulation de distribution #%d',
                    $distribution->getId()
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

    /**
     * Déduit le stock lors de la réactivation d'une distribution annulée
     */
    public function deductFromReactivatedDistribution(Distribution $distribution): bool
    {
        $product = $distribution->getProduct();
        $quantity = $distribution->getQuantity();

        if (!$product || $quantity <= 0) {
            return false;
        }

        $entityManager = $this->getEntityManager();

        // Rechercher le stock existant pour ce produit
        $stock = $this->findOneBy(['product' => $product]);

        if (!$stock) {
            // Pas de stock disponible
            return false;
        }

        // Vérifier si le stock est suffisant
        if ($stock->getQuantity() < $quantity) {
            // Stock insuffisant
            return false;
        }

        // Déduire la quantité du stock
        $stock->setQuantity($stock->getQuantity() - $quantity);
        $stock->setNote(
            sprintf(
                'Déduction suite à réactivation de distribution #%d',
                $distribution->getId()
            )
        );

        $entityManager->flush();

        return true;
    }

    /**
     * Vérifie si le stock est suffisant pour une distribution
     */
    public function hasEnoughStock(Distribution $distribution): bool
    {
        $product = $distribution->getProduct();
        $quantity = $distribution->getQuantity();

        if (!$product || $quantity <= 0) {
            return false;
        }

        $stock = $this->findOneBy(['product' => $product]);

        if (!$stock) {
            return false;
        }

        return $stock->getQuantity() >= $quantity;
    }
}
