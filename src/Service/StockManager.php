<?php

namespace App\Service;

use App\Entity\Distribution;
use App\Entity\Intervention;
use App\Entity\Product;
use App\Entity\Stock;
use App\Entity\Warehouse;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;

class StockManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StockRepository        $stockRepository,
    ) {}

    /**
     * Déduit le stock d'un entrepôt.
     * Si aucun entrepôt n'est fourni, cherche le premier avec du stock.
     */
    public function deductWarehouseStock(Product $product, int $quantity, ?Warehouse $warehouse = null): void
    {
        if ($quantity <= 0) {
            return;
        }

        $stock = null;

        if ($warehouse) {
            $stock = $this->stockRepository->findOneBy([
                'product' => $product,
                'warehouse' => $warehouse,
            ]);
        } else {
            // Chercher le premier entrepôt avec du stock
            $stocks = $this->stockRepository->findBy(['product' => $product]);
            foreach ($stocks as $s) {
                if ($s->getQuantity() > 0) {
                    $stock = $s;
                    break;
                }
            }
        }

        if (!$stock || $stock->getQuantity() < $quantity) {
            $available = $stock ? $stock->getQuantity() : 0;
            throw new \Exception(sprintf(
                'Stock insuffisant en entrepôt pour le produit "%s". Disponible: %d, Demandé: %d',
                $product->getName(),
                $available,
                $quantity
            ));
        }

        $stock->setQuantity($stock->getQuantity() - $quantity);
        $this->entityManager->flush();
    }

    /**
     * Restaure le stock d'un entrepôt.
     */
    public function restoreWarehouseStock(Product $product, int $quantity, ?Warehouse $warehouse = null): void
    {
        if ($quantity <= 0) {
            return;
        }

        if (!$warehouse) {
            // Par défaut, on restaure dans le premier entrepôt trouvé ou le premier de la liste
            $stock = $this->stockRepository->findOneBy(['product' => $product]);
            if (!$stock) {
                $warehouse = $this->entityManager->getRepository(Warehouse::class)->findOneBy([]);
                if (!$warehouse) {
                    return; // Pas d'entrepôt du tout
                }
                $stock = new Stock();
                $stock->setProduct($product);
                $stock->setWarehouse($warehouse);
                $stock->setQuantity(0);
                $this->entityManager->persist($stock);
            }
        } else {
            $stock = $this->stockRepository->findOneBy([
                'product' => $product,
                'warehouse' => $warehouse,
            ]);
            if (!$stock) {
                $stock = new Stock();
                $stock->setProduct($product);
                $stock->setWarehouse($warehouse);
                $stock->setQuantity(0);
                $this->entityManager->persist($stock);
            }
        }

        $stock->setQuantity($stock->getQuantity() + $quantity);
        $this->entityManager->flush();
    }
}
