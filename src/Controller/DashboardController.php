<?php

namespace App\Controller;

use App\Repository\StockRepository;
use App\Repository\ProductRepository;
use App\Repository\RackRepository;
use App\Repository\WarehouseRepository;
use App\Repository\DistributionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        StockRepository $stockRepo,
        ProductRepository $productRepo,
        RackRepository $rackRepo,
        WarehouseRepository $warehouseRepo,
        DistributionRepository $distributionRepo,
        UserRepository $userRepo
    ): Response {
        // ============ STATISTIQUES PRINCIPALES ============

        // Total des produits en stock (somme des quantités dans tous les stocks)
        $totalStock = $stockRepo->createQueryBuilder('s')
            ->select('SUM(s.quantity)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // Nombre total de produits différents
        $totalProducts = $productRepo->count([]);

        // Nombre total de rayons
        $totalRacks = $rackRepo->count([]);

        // Nombre total d'entrepôts
        $totalWarehouses = $warehouseRepo->count([]);

        // Total des quantités distribuées (somme des quantités de toutes les distributions)
        $totalDistributedQuantity = $distributionRepo->createQueryBuilder('d')
            ->select('SUM(d.quantity)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // Nombre total de distributions
        $totalDistributions = $distributionRepo->count([]);

        // ============ DISTRIBUTIONS PAR STATUT ============
        $distributionsPreparing = $distributionRepo->count(['status' => 'preparing']);
        $distributionsInProgress = $distributionRepo->count(['status' => 'in_progress']);
        $distributionsDelivered = $distributionRepo->count(['status' => 'delivered']);

        // ============ UTILISATEURS PAR RÔLE ============
        $adminCount = $userRepo->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.role LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getSingleScalarResult();

        $driverCount = $userRepo->count(['role' => 'ROLE_DRIVER']);
        $statisticsCount = $userRepo->count(['role' => 'ROLE_STATISTICS']);

        // ============ POURCENTAGES POUR BARRES DE PROGRESSION ============

        // Pourcentage de stock disponible (basé sur la capacité totale des rayons)
        $totalRackCapacity = $rackRepo->createQueryBuilder('r')
            ->select('SUM(r.requiredQuantity)')
            ->getQuery()
            ->getSingleScalarResult() ?? 1;

        $currentRackStock = $rackRepo->createQueryBuilder('r')
            ->select('SUM(r.currentQuantity)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        $stockPercentage = $totalRackCapacity > 0
            ? round(($currentRackStock / $totalRackCapacity) * 100)
            : 0;

        // Pourcentage de distributions complétées
        $distributionPercentage = $totalDistributions > 0
            ? round(($distributionsDelivered / $totalDistributions) * 100)
            : 0;

        // Pourcentage de rayons actifs (ayant un produit assigné)
        $activeRacks = $rackRepo->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.product IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $activeRacksPercentage = $totalRacks > 0
            ? round(($activeRacks / $totalRacks) * 100)
            : 0;

        // ============ DONNÉES POUR LE GRAPHIQUE (12 derniers mois) ============
        $monthlyDistributions = [];
        $currentYear = date('Y');

        for ($month = 1; $month <= 12; $month++) {
            // Créer les dates de début et fin du mois
            $startDate = new \DateTime("$currentYear-$month-01 00:00:00");
            $endDate = (clone $startDate)->modify('last day of this month')->setTime(23, 59, 59);

            $quantity = $distributionRepo->createQueryBuilder('d')
                ->select('SUM(d.quantity)')
                ->where('d.createdAt >= :startDate')
                ->andWhere('d.createdAt <= :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
                ->getQuery()
                ->getSingleScalarResult() ?? 0;

            $monthlyDistributions[] = (int) $quantity;
        }

        // ============ ACTIVITÉS RÉCENTES ============
        $recentActivities = $distributionRepo->createQueryBuilder('d')
            ->leftJoin('d.product', 'p')
            ->leftJoin('d.user', 'u')
            ->orderBy('d.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Enrichir les activités avec les classes de badge
        foreach ($recentActivities as $activity) {
            $activity->statusLabel = 'distribution.status.' . $activity->getStatus();
            $activity->statusBadgeClass = match($activity->getStatus()) {
                'delivered' => 'bg-success',
                'in_progress' => 'bg-primary',
                'preparing' => 'bg-warning',
                default => 'bg-secondary',
            };
        }

        return $this->render('dashboard/index.html.twig', [
            // ============ STATISTIQUES PRINCIPALES ============
            'total_stock' => $totalStock,
            'total_products' => $totalProducts,
            'total_racks' => $totalRacks,
            'total_warehouses' => $totalWarehouses,
            'total_distributed_quantity' => $totalDistributedQuantity,
            'total_distributions' => $totalDistributions,

            // ============ DISTRIBUTIONS PAR STATUT ============
            'distributions_preparing' => $distributionsPreparing,
            'distributions_in_progress' => $distributionsInProgress,
            'distributions_delivered' => $distributionsDelivered,

            // ============ UTILISATEURS PAR RÔLE ============
            'admin_count' => $adminCount,
            'driver_count' => $driverCount,
            'statistics_count' => $statisticsCount,

            // ============ POURCENTAGES POUR BARRES DE PROGRESSION ============
            'stock_percentage' => $stockPercentage,
            'distribution_percentage' => $distributionPercentage,
            'active_racks_percentage' => $activeRacksPercentage,

            // ============ DONNÉES POUR LE GRAPHIQUE (12 mois) ============
            'monthly_distributions' => $monthlyDistributions,

            // ============ ACTIVITÉS RÉCENTES ============
            'recent_activities' => $recentActivities,
        ]);
    }
}
