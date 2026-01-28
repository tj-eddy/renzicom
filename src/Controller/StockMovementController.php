<?php

namespace App\Controller;

use App\Repository\StockMovementRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
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

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
                h1 { color: #333; font-size: 16px; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th { background-color: #4472C4; color: white; padding: 8px; text-align: left; font-size: 9px; }
                td { border: 1px solid #ddd; padding: 6px; font-size: 9px; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .badge-success { background-color: #28a745; color: white; padding: 2px 6px; border-radius: 3px; }
                .badge-danger { background-color: #dc3545; color: white; padding: 2px 6px; border-radius: 3px; }
                .badge-info { background-color: #17a2b8; color: white; padding: 2px 6px; border-radius: 3px; }
                .badge-warning { background-color: #ffc107; color: black; padding: 2px 6px; border-radius: 3px; }
                .badge-primary { background-color: #007bff; color: white; padding: 2px 6px; border-radius: 3px; }
                .footer { margin-top: 20px; font-size: 8px; color: #666; }
            </style>
        </head>
        <body>
            <h1>Historique des mouvements de stock</h1>
            <p>Date d\'export: ' . date('d/m/Y H:i:s') . '</p>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Produit</th>
                        <th>Entrepot</th>
                        <th>Quantite</th>
                        <th>Type</th>
                        <th>Utilisateur</th>
                        <th>Commentaire</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($movements as $movement) {
            $qty = $movement->getQuantity();
            $qtyClass = $qty > 0 ? 'badge-success' : 'badge-danger';
            $qtyDisplay = ($qty > 0 ? '+' : '') . $qty;

            $type = $movement->getType();
            $typeClass = match ($type) {
                'IN' => 'badge-success',
                'OUT' => 'badge-danger',
                'INITIAL' => 'badge-info',
                'ADJUSTMENT' => 'badge-warning',
                'RETURN' => 'badge-primary',
                default => 'badge-info',
            };

            $html .= '<tr>
                <td>' . ($movement->getCreatedAt()?->format('d/m/Y H:i') ?? '') . '</td>
                <td>' . htmlspecialchars($movement->getProduct()?->getName() ?? 'N/A') . '</td>
                <td>' . htmlspecialchars($movement->getWarehouse()?->getName() ?? 'N/A') . '</td>
                <td><span class="' . $qtyClass . '">' . $qtyDisplay . '</span></td>
                <td><span class="' . $typeClass . '">' . $type . '</span></td>
                <td>' . htmlspecialchars($movement->getUser()?->getName() ?? 'Systeme') . '</td>
                <td>' . htmlspecialchars($movement->getComment() ?? '-') . '</td>
            </tr>';
        }

        $html .= '</tbody></table>
            <div class="footer">Total: ' . count($movements) . ' mouvements</div>
        </body>
        </html>';

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="mouvements_stock_' . date('Y-m-d_H-i-s') . '.pdf"',
            ]
        );
    }
}
