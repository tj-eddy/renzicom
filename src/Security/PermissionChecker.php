<?php

namespace App\Security;

use Symfony\Bundle\SecurityBundle\Security;

class PermissionChecker
{
    public function __construct(
        private Security $security
    ) {
    }

    /**
     * Vérifie si l'utilisateur peut créer des produits ou entrepôts
     * Admin: ✅ | Livreur: ❌ | Statistique: ❌
     */
    public function canCreateProductOrWarehouse(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    /**
     * Vérifie si l'utilisateur peut créer des distributions
     * Admin: ✅ | Livreur: ✅ | Statistique: ❌
     */
    public function canCreateDistribution(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN')
            || $this->security->isGranted('ROLE_DRIVER');
    }

    /**
     * Vérifie si l'utilisateur peut créer des interventions
     * Admin: ✅ | Livreur: ✅ | Statistique: ❌
     */
    public function canCreateIntervention(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN')
            || $this->security->isGranted('ROLE_DRIVER');
    }

    /**
     * Vérifie si l'utilisateur peut modifier des distributions
     * Admin: ✅ | Livreur: ✅ | Statistique: ❌
     */
    public function canEditDistribution(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN')
            || $this->security->isGranted('ROLE_DRIVER');
    }

    /**
     * Vérifie si l'utilisateur peut supprimer (tout type d'entité)
     * Admin: ✅ | Livreur: ❌ | Statistique: ❌
     */
    public function canDelete(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    /**
     * Vérifie si l'utilisateur peut gérer les utilisateurs
     * Admin: ✅ | Livreur: ❌ | Statistique: ❌
     */
    public function canManageUsers(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    /**
     * Vérifie si l'utilisateur peut voir (lecture seule)
     * Admin: ✅ | Livreur: ✅ | Statistique: ✅
     */
    public function canView(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN')
            || $this->security->isGranted('ROLE_DRIVER')
            || $this->security->isGranted('ROLE_STATISTICS');
    }
}
