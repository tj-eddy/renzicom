<?php
// src/Service/ProductStockExportService.php

namespace App\Service;

use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class ProductStockExportService
{
    public function __construct(
        private ProductRepository $productRepository,
        private StockRepository   $stockRepository,
        #[Autowire('%kernel.project_dir%')]
        private string            $projectDir
    )
    {
    }

    public function exportToCSV(): string
    {
        $filename = 'products_stock_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = $this->projectDir . '/public/exports/' . $filename;

        // Créer le dossier si nécessaire
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $products = $this->productRepository->findAll();

        $handle = fopen($filepath, 'w');

        // BOM UTF-8 pour Excel
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // En-têtes
        $headers = [
            'ID',
            'Nom',
            'Année d\'édition',
            'Langue',
            'Stock Total',
            'Détail Stock (Rack)',
            'Nombre de Racks',
            'Date de création',
        ];

        fputcsv($handle, $headers, ';');

        // Données
        foreach ($products as $product) {
            $stocks = $this->stockRepository->findBy(['product' => $product]);
            $productTotalStock = 0;
            $stockDetails = [];

            foreach ($stocks as $stock) {
                $qty = $stock->getQuantity() ?? 0;
                $productTotalStock += $qty;
                $stockDetails[] = sprintf(
                    '%s (%d)',
                    $stock->getRack()?->getName() ?? 'N/A',
                    $qty
                );
            }

            $row = [
                $product->getId(),
                $product->getName(),
                $product->getYearEdition() ?? '',
                $product->getLanguage() ?? '',
                $productTotalStock,
                !empty($stockDetails) ? implode(' | ', $stockDetails) : 'Aucun stock',
                count($stocks),
                $product->getCreatedAt()?->format('Y-m-d H:i:s') ?? '',
            ];

            fputcsv($handle, $row, ';');
        }

        fclose($handle);

        return $filename;
    }

    public function getExportPath(string $filename): string
    {
        return $this->projectDir . '/public/exports/' . $filename;
    }
}
