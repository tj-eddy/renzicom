<?php
// src/Controller/ExportController.php

namespace App\Controller;

use App\Service\ProductStockExportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/export')]
class ExportController extends AbstractController
{
    #[Route('/products-stock/generate', name: 'app_export_products_stock_generate', methods: ['POST'])]
    public function generateExport(ProductStockExportService $exportService): JsonResponse
    {
        try {
            // Générer l'export sans images
            $filename = $exportService->exportToCSV();

            return $this->json([
                'success' => true,
                'filename' => $filename,
                'download_url' => $this->generateUrl('app_export_products_stock_download', ['filename' => $filename])
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la génération de l\'export : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/products-stock/download/{filename}', name: 'app_export_products_stock_download', methods: ['GET'])]
    public function downloadExport(string $filename, ProductStockExportService $exportService): BinaryFileResponse
    {
        $filepath = $exportService->getExportPath($filename);

        if (!file_exists($filepath)) {
            throw $this->createNotFoundException('Fichier d\'export introuvable');
        }

        $response = new BinaryFileResponse($filepath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        // Supprimer le fichier après téléchargement
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
