<?php

// src/Controller/DashboardController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            // ============ STATISTIQUES PRINCIPALES ============
            'total_stock' => 15750,
            'total_products' => 45,
            'total_racks' => 120,
            'total_warehouses' => 5,
            'total_distributed_quantity' => 8420,
            'total_distributions' => 87,

            // ============ DISTRIBUTIONS PAR STATUT ============
            'distributions_preparing' => 12,
            'distributions_in_progress' => 8,
            'distributions_delivered' => 67,

            // ============ UTILISATEURS PAR RÔLE ============
            'admin_count' => 3,
            'driver_count' => 15,
            'statistics_count' => 5,

            // ============ POURCENTAGES POUR BARRES DE PROGRESSION ============
            'stock_percentage' => 75,
            'distribution_percentage' => 85,
            'active_racks_percentage' => 92,

            // ============ DONNÉES POUR LE GRAPHIQUE (12 mois) ============
            'monthly_distributions' => [450, 520, 680, 720, 890, 950, 1020, 980, 1150, 1200, 1100, 950],

            // ============ ACTIVITÉS RÉCENTES ============
            'recent_activities' => [
                (object) [
                    'product' => (object) ['name' => 'Paris Match'],
                    'quantity' => 50,
                    'user' => (object) ['name' => 'Jean Dupont'],
                    'destination' => 'Hôtel Marriott, Hôtel Hilton',
                    'createAt' => new \DateTime('2025-01-22 14:30:00'),
                    'statusLabel' => 'distribution.status.delivered',
                    'statusBadgeClass' => 'bg-success',
                ],
                (object) [
                    'product' => (object) ['name' => 'Elle Magazine'],
                    'quantity' => 35,
                    'user' => (object) ['name' => 'Marie Martin'],
                    'destination' => 'Hôtel Sofitel, Hôtel Novotel',
                    'createAt' => new \DateTime('2025-01-22 11:15:00'),
                    'statusLabel' => 'distribution.status.in_progress',
                    'statusBadgeClass' => 'bg-primary',
                ],
                (object) [
                    'product' => (object) ['name' => 'Geo Magazine'],
                    'quantity' => 28,
                    'user' => (object) ['name' => 'Pierre Leroux'],
                    'destination' => 'Hôtel Ibis, Hôtel Mercure',
                    'createAt' => new \DateTime('2025-01-22 09:45:00'),
                    'statusLabel' => 'distribution.status.delivered',
                    'statusBadgeClass' => 'bg-success',
                ],
                (object) [
                    'product' => (object) ['name' => 'Le Point'],
                    'quantity' => 42,
                    'user' => (object) ['name' => 'Sophie Bernard'],
                    'destination' => 'Hôtel Pullman, Hôtel Accor',
                    'createAt' => new \DateTime('2025-01-21 16:20:00'),
                    'statusLabel' => 'distribution.status.preparing',
                    'statusBadgeClass' => 'bg-warning',
                ],
                (object) [
                    'product' => (object) ['name' => 'L\'Express'],
                    'quantity' => 60,
                    'user' => (object) ['name' => 'Thomas Petit'],
                    'destination' => 'Hôtel Renaissance Paris',
                    'createAt' => new \DateTime('2025-01-21 14:00:00'),
                    'statusLabel' => 'distribution.status.delivered',
                    'statusBadgeClass' => 'bg-success',
                ],
            ],
        ]);
    }
}
