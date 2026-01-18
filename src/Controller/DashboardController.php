<?php

namespace App\Controller;

use App\Entity\Distribution;
use App\Entity\Product;
use App\Entity\Rack;
use App\Entity\Stock;
use App\Entity\User;
use App\Entity\Warehouse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Statistiques des stocks
        $totalStock = $entityManager->getRepository(Stock::class)
            ->createQueryBuilder('s')
            ->select('SUM(s.quantity)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        $totalProducts = $entityManager->getRepository(Product::class)->count([]);
        $totalRacks = $entityManager->getRepository(Rack::class)->count([]);
        $totalWarehouses = $entityManager->getRepository(Warehouse::class)->count([]);

        // Statistiques des distributions
        $totalDistributions = $entityManager->getRepository(Distribution::class)->count([]);

        $totalDistributedQuantity = $entityManager->getRepository(Distribution::class)
            ->createQueryBuilder('d')
            ->select('SUM(d.quantity)')
            ->where('d.status = :status')
            ->setParameter('status', Distribution::STATUS_DELIVERED)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        $distributionsInProgress = $entityManager->getRepository(Distribution::class)
            ->count(['status' => Distribution::STATUS_IN_PROGRESS]);

        $distributionsPreparing = $entityManager->getRepository(Distribution::class)
            ->count(['status' => Distribution::STATUS_PREPARING]);

        // Statistiques des utilisateurs par rôle
        $adminCount = $entityManager->getRepository(User::class)
            ->count(['role' => 'ROLE_ADMIN']);

        $driverCount = $entityManager->getRepository(User::class)
            ->count(['role' => 'ROLE_DRIVER']);

        $statisticsCount = $entityManager->getRepository(User::class)
            ->count(['role' => 'ROLE_STATISTICS']);

        $totalUsers = $adminCount + $driverCount + $statisticsCount;

        // Distributions mensuelles pour le graphique (année en cours)
        $currentYear = date('Y');
        $monthlyDistributions = [];

        for ($month = 1; $month <= 12; $month++) {
            // Créer les dates de début et fin du mois
            $startDate = new \DateTime("$currentYear-$month-01 00:00:00");
            $endDate = (clone $startDate)->modify('last day of this month')->setTime(23, 59, 59);

            $count = $entityManager->getRepository(Distribution::class)
                ->createQueryBuilder('d')
                ->select('SUM(d.quantity)')
                ->where('d.createAt >= :startDate')
                ->andWhere('d.createAt <= :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
                ->getQuery()
                ->getSingleScalarResult() ?? 0;

            $monthlyDistributions[] = (int) $count;
        }

        // Activités récentes (10 dernières distributions)
        $recentActivities = $entityManager->getRepository(Distribution::class)
            ->createQueryBuilder('d')
            ->leftJoin('d.user', 'u')
            ->addSelect('u')
            ->leftJoin('d.product', 'p')
            ->addSelect('p')
            ->orderBy('d.createAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Calcul des pourcentages
        $stockCapacity = 10000; // Capacité totale estimée (à ajuster selon tes besoins)
        $stockPercentage = $stockCapacity > 0 ? round(($totalStock / $stockCapacity) * 100) : 0;

        $distributionPercentage = $totalStock > 0 ? round(($totalDistributedQuantity / ($totalStock + $totalDistributedQuantity)) * 100) : 0;

        $activeRacksPercentage = $totalRacks > 0 ? round((count($this->getActiveRacks($entityManager)) / $totalRacks) * 100) : 0;

        return $this->render('dashboard/index.html.twig', [
            // Statistiques principales
            'total_stock' => $totalStock,
            'total_products' => $totalProducts,
            'total_racks' => $totalRacks,
            'total_warehouses' => $totalWarehouses,
            'total_distributions' => $totalDistributions,
            'total_distributed_quantity' => $totalDistributedQuantity,
            'distributions_in_progress' => $distributionsInProgress,
            'distributions_preparing' => $distributionsPreparing,

            // Statistiques utilisateurs
            'admin_count' => $adminCount,
            'driver_count' => $driverCount,
            'statistics_count' => $statisticsCount,
            'total_users' => $totalUsers,

            // Données pour le graphique
            'monthly_distributions' => $monthlyDistributions,

            // Activités récentes
            'recent_activities' => $recentActivities,

            // Pourcentages
            'stock_percentage' => min($stockPercentage, 100),
            'distribution_percentage' => $distributionPercentage,
            'active_racks_percentage' => $activeRacksPercentage,
        ]);
    }

    /**
     * Récupère les racks qui ont du stock
     */
    private function getActiveRacks(EntityManagerInterface $entityManager): array
    {
        return $entityManager->getRepository(Stock::class)
            ->createQueryBuilder('s')
            ->select('DISTINCT IDENTITY(s.rack)')
            ->where('s.quantity > 0')
            ->getQuery()
            ->getResult();
    }
}
