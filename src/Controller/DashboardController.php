<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(UserRepository $userRepository): Response
    {
        $totalUsers = $userRepository->count([]);
        $adminCount = $userRepository->count(['role' => 'ROLE_ADMIN']);
        $driverCount = $userRepository->count(['role' => 'ROLE_DRIVER']);
        $statisticsCount = $userRepository->count(['role' => 'ROLE_STATISTICS']);

        return $this->render('dashboard/index.html.twig', [
            'total_users' => $totalUsers,
            'admin_count' => $adminCount,
            'driver_count' => $driverCount,
            'statistics_count' => $statisticsCount,
        ]);
    }
}
