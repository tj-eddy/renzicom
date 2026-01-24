<?php

namespace App\Repository;

use App\Entity\Intervention;
use App\Entity\Rack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rack>
 */
class RackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rack::class);
    }

    /**
     * Met à jour la quantité actuelle d'un rack lors d'une intervention.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateCurrentQuantityFromIntervention(Intervention $intervention): array
    {
        $rack = $intervention->getRack();
        $quantityAdded = $intervention->getQuantityAdded();
        $currentQuantity = $rack->getCurrentQuantity();
        $requiredQuantity = $rack->getRequiredQuantity();

        // Vérifier si le rack est déjà plein
        if ($currentQuantity >= $requiredQuantity) {
            return [
                'success' => false,
                'message' => 'rack.messages.already_full',
            ];
        }

        // Calculer la nouvelle quantité
        $newQuantity = $currentQuantity + $quantityAdded;

        // Vérifier si la quantité ajoutée dépasse la capacité requise
        if ($newQuantity > $requiredQuantity) {
            return [
                'success' => false,
                'message' => 'rack.messages.exceeds_capacity',
                'params' => [
                    '%added%' => $quantityAdded,
                    '%available%' => $requiredQuantity - $currentQuantity,
                ],
            ];
        }

        // Mettre à jour la quantité actuelle
        $rack->setCurrentQuantity($newQuantity);

        $this->getEntityManager()->persist($rack);
        $this->getEntityManager()->flush();

        return [
            'success' => true,
            'message' => 'rack.messages.updated_quantity',
            'params' => [
                '%new%' => $newQuantity,
                '%required%' => $requiredQuantity,
            ],
        ];
    }

    /**
     * Vérifie si un rack peut recevoir une quantité donnée.
     */
    public function canAddQuantity(Rack $rack, int $quantity): bool
    {
        $currentQuantity = $rack->getCurrentQuantity();
        $requiredQuantity = $rack->getRequiredQuantity();

        return ($currentQuantity + $quantity) <= $requiredQuantity;
    }

    /**
     * Retourne l'espace disponible dans un rack.
     */
    public function getAvailableSpace(Rack $rack): int
    {
        return $rack->getRequiredQuantity() - $rack->getCurrentQuantity();
    }
}
