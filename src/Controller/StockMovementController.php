<?php

namespace App\Controller;

use App\Repository\StockMovementRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stock-movement')]
class StockMovementController extends AbstractController
{
    #[Route('/', name: 'app_stock_movement_index', methods: ['GET'])]
    public function index(StockMovementRepository $stockMovementRepository): Response
    {
        return $this->render('stock_movement/index.html.twig', [
            'movements' => $stockMovementRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/export/csv', name: 'app_stock_movement_export_csv', methods: ['GET'])]
    public function exportCsv(StockMovementRepository $stockMovementRepository): Response
    {
        $movements = $stockMovementRepository->findBy([], ['createdAt' => 'DESC']);

        $response = new StreamedResponse(function () use ($movements) {
            $handle = fopen('php://output', 'w+');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Date', 'Produit', 'Entrepot', 'Quantite', 'Type', 'Utilisateur', 'Commentaire'], ';');

            foreach ($movements as $movement) {
                fputcsv($handle, [
                    $movement->getCreatedAt()?->format('d/m/Y H:i:s') ?? '',
                    $movement->getProduct()?->getName() ?? 'N/A',
                    $movement->getWarehouse()?->getName() ?? 'N/A',
                    $movement->getQuantity(),
                    $movement->getType(),
                    $movement->getUser()?->getName() ?? 'Systeme',
                    $movement->getComment() ?? '',
                ], ';');
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="mouvements_stock_' . date('Y-m-d_H-i-s') . '.csv"');

        return $response;
    }

    #[Route('/export/pdf', name: 'app_stock_movement_export_pdf', methods: ['GET'])]
    public function exportPdf(StockMovementRepository $stockMovementRepository): Response
    {
        $movements = $stockMovementRepository->findBy([], ['createdAt' => 'DESC']);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Mouvements de stock');

        $headers = ['Date', 'Produit', 'Entrepot', 'Quantite', 'Type', 'Utilisateur', 'Commentaire'];
        $sheet->fromArray($headers, null, 'A1');

        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle('A1:G1')->getFont()->getColor()->setARGB('FFFFFFFF');

        $row = 2;
        foreach ($movements as $movement) {
            $sheet->setCellValue('A' . $row, $movement->getCreatedAt()?->format('d/m/Y H:i:s') ?? '');
            $sheet->setCellValue('B' . $row, $movement->getProduct()?->getName() ?? 'N/A');
            $sheet->setCellValue('C' . $row, $movement->getWarehouse()?->getName() ?? 'N/A');
            $sheet->setCellValue('D' . $row, $movement->getQuantity());
            $sheet->setCellValue('E' . $row, $movement->getType());
            $sheet->setCellValue('F' . $row, $movement->getUser()?->getName() ?? 'Systeme');
            $sheet->setCellValue('G' . $row, $movement->getComment() ?? '');
            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="mouvements_stock_' . date('Y-m-d_H-i-s') . '.xlsx"');

        return $response;
    }
}
