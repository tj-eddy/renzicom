<?php

namespace App\Service;

use App\Entity\Distribution;
use App\Entity\Intervention;
use App\Entity\Product;
use App\Entity\Stock;
use App\Entity\Warehouse;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\StockMovement;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class StockManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StockRepository        $stockRepository,
        private readonly Security               $security,
    ) {}

    /**
     * Déduit le stock d'un entrepôt.
     */
    public function deductWarehouseStock(Product $product, int $quantity, ?Warehouse $warehouse = null, string $type = StockMovement::TYPE_OUT, ?string $comment = null): void
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
            $stocks = $this->stockRepository->findBy(['product' => $product]);
            foreach ($stocks as $s) {
                if ($s->getQuantity() > 0) {
                    $stock = $s;
                    $warehouse = $s->getWarehouse();
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
        $this->logMovement($product, $warehouse, -$quantity, $type, $comment);

        $this->entityManager->flush();
    }

    /**
     * Restaure le stock d'un entrepôt.
     */
    public function restoreWarehouseStock(Product $product, int $quantity, ?Warehouse $warehouse = null, string $type = StockMovement::TYPE_RETURN, ?string $comment = null): void
    {
        if ($quantity <= 0) {
            return;
        }

        if (!$warehouse) {
            $stock = $this->stockRepository->findOneBy(['product' => $product]);
            if (!$stock) {
                $warehouse = $this->entityManager->getRepository(Warehouse::class)->findOneBy([]);
                if (!$warehouse) {
                    return;
                }
            } else {
                $warehouse = $stock->getWarehouse();
            }
        }

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

        $stock->setQuantity($stock->getQuantity() + $quantity);
        $this->logMovement($product, $warehouse, $quantity, $type, $comment);

        $this->entityManager->flush();
    }

    /**
     * Applique un ajustement de stock manuel.
     */
    public function applyStockAdjustment(Stock $stock, int $newQuantity, ?string $comment = null): void
    {
        $oldQuantity = $stock->getQuantity();
        $delta = $newQuantity - $oldQuantity;

        if ($delta === 0) {
            return;
        }

        $stock->setQuantity($newQuantity);
        $this->logMovement(
            $stock->getProduct(),
            $stock->getWarehouse(),
            $delta,
            $oldQuantity === 0 && $delta > 0 ? StockMovement::TYPE_INITIAL : StockMovement::TYPE_ADJUSTMENT,
            $comment
        );

        $this->entityManager->flush();
    }

    /**
     * Enregistre un mouvement de stock.
     */
    private function logMovement(Product $product, Warehouse $warehouse, int $quantity, string $type, ?string $comment = null): void
    {
        $movement = new StockMovement();
        $movement->setProduct($product);
        $movement->setWarehouse($warehouse);
        $movement->setQuantity($quantity);
        $movement->setType($type);
        $movement->setComment($comment);

        $user = $this->security->getUser();
        if ($user instanceof User) {
            $movement->setUser($user);
        }

        $this->entityManager->persist($movement);
    }
}
