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
        private EntityManagerInterface $entityManager,
        private StockRepository $stockRepository
    ) {
    }

    /**
     * Déduit le stock de l'entrepôt lors de la création d'une distribution
     * (Le livreur charge sa voiture)
     */
    public function deductStockForDistribution(Distribution $distribution): bool
    {
        $product = $distribution->getProduct();
        $quantity = $distribution->getQuantity();

        if (!$product || $quantity <= 0) {
            return false;
        }

        // Chercher le stock disponible dans les entrepôts pour ce produit
        $stocks = $this->stockRepository->createQueryBuilder('s')
            ->where('s.product = :product')
            ->andWhere('s.quantity > 0')
            ->setParameter('product', $product)
            ->orderBy('s.createdAt', 'ASC') // FIFO
            ->getQuery()
            ->getResult();

        $remainingQuantity = $quantity;

        foreach ($stocks as $stock) {
            if ($remainingQuantity <= 0) {
                break;
            }

            if ($stock->getQuantity() >= $remainingQuantity) {
                $stock->setQuantity($stock->getQuantity() - $remainingQuantity);
                $remainingQuantity = 0;
            } else {
                $remainingQuantity -= $stock->getQuantity();
                $stock->setQuantity(0);
            }
        }

        if ($remainingQuantity > 0) {
            throw new \Exception(
                sprintf(
                    'Stock insuffisant pour le produit "%s". Demandé: %d, Disponible: %d',
                    $product->getName(),
                    $quantity,
                    $quantity - $remainingQuantity
                )
            );
        }

        $this->entityManager->flush();
        return true;
    }

    /**
     * Restaure le stock de l'entrepôt lors de l'annulation d'une distribution
     * OU lors du retour de marchandises non distribuées
     */
    public function restoreStockForDistribution(Distribution $distribution, ?Warehouse $warehouse = null): void
    {
        $product = $distribution->getProduct();
        $quantity = $distribution->getQuantity();

        if (!$product || $quantity <= 0) {
            return;
        }

        if (!$warehouse) {
            $warehouse = $this->entityManager->getRepository(Warehouse::class)->findOneBy([]);
        }

        if (!$warehouse) {
            throw new \Exception('Aucun entrepôt disponible pour restaurer le stock');
        }

        $stock = $this->stockRepository->findOneBy([
            'product' => $product,
            'warehouse' => $warehouse
        ]);

        if ($stock) {
            $stock->setQuantity($stock->getQuantity() + $quantity);
        } else {
            $stock = new Stock();
            $stock->setProduct($product);
            $stock->setWarehouse($warehouse);
            $stock->setQuantity($quantity);
            $stock->setNote('Stock restauré depuis distribution #' . $distribution->getId());
            $this->entityManager->persist($stock);
        }

        $this->entityManager->flush();
    }

    /**
     * Retourne les marchandises non distribuées à l'entrepôt
     * (Fin de tournée avec reste)
     */
    public function returnRemainingStock(Distribution $distribution, ?Warehouse $warehouse = null): void
    {
        $product = $distribution->getProduct();
        $remainingQuantity = $distribution->getQuantityRemaining();

        if (!$product || $remainingQuantity <= 0) {
            return;
        }

        if (!$warehouse) {
            $warehouse = $this->entityManager->getRepository(Warehouse::class)->findOneBy([]);
        }

        if (!$warehouse) {
            throw new \Exception('Aucun entrepôt disponible');
        }

        $stock = $this->stockRepository->findOneBy([
            'product' => $product,
            'warehouse' => $warehouse
        ]);

        if ($stock) {
            $stock->setQuantity($stock->getQuantity() + $remainingQuantity - $distribution->getQuantity());
        } else {
            $stock = new Stock();
            $stock->setProduct($product);
            $stock->setWarehouse($warehouse);
            $stock->setQuantity($remainingQuantity);
            $stock->setNote(sprintf(
                'Retour distribution #%d - %d magazines non distribués',
                $distribution->getId(),
                $remainingQuantity
            ));
            $this->entityManager->persist($stock);
        }

        $this->entityManager->flush();
    }

    /**
     * Met à jour le stock du rack lors d'une intervention
     */
    public function updateRackStockForIntervention(Intervention $intervention): void
    {
        $rack = $intervention->getRack();
        $quantityAdded = $intervention->getQuantityAdded();
        $distribution = $intervention->getDistribution();

        if (!$rack || $quantityAdded <= 0) {
            return;
        }

        // Vérifier qu'on ne dépasse pas la quantité disponible dans la distribution
        $quantityRemaining = $distribution->getQuantity() - $distribution->getQuantityDistributed();

        if ($quantityAdded > $quantityRemaining + $quantityAdded) {
            throw new \Exception(sprintf(
                'Quantité insuffisante dans la distribution. Disponible: %d, Demandé: %d',
                $quantityRemaining + $quantityAdded,
                $quantityAdded
            ));
        }

        // Mettre à jour la quantité actuelle du rack
        $newQuantity = $rack->getCurrentQuantity() + $quantityAdded;
        $rack->setCurrentQuantity($newQuantity);

        $this->entityManager->flush();
    }

    /**
     * Annule la mise à jour du rack lors de la suppression d'une intervention
     */
    public function revertRackStockForIntervention(Intervention $intervention): void
    {
        $rack = $intervention->getRack();
        $quantityAdded = $intervention->getQuantityAdded();

        if (!$rack || $quantityAdded <= 0) {
            return;
        }

        $newQuantity = max(0, $rack->getCurrentQuantity() - $quantityAdded);
        $rack->setCurrentQuantity($newQuantity);

        $this->entityManager->flush();
    }

    /**
     * Vérifie si le stock est suffisant pour une distribution
     */
    public function hasEnoughStock(Product $product, int $quantity): bool
    {
        $totalStock = $this->stockRepository->createQueryBuilder('s')
            ->select('SUM(s.quantity)')
            ->where('s.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();

        return $totalStock >= $quantity;
    }

    /**
     * Obtient le stock total disponible pour un produit
     */
    public function getTotalStockForProduct(Product $product): int
    {
        $totalStock = $this->stockRepository->createQueryBuilder('s')
            ->select('SUM(s.quantity)')
            ->where('s.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($totalStock ?? 0);
    }
}
