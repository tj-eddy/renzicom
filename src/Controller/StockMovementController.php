<?php

namespace App\Controller;

use App\Repository\StockMovementRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/stock-movement')]
class StockMovementController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {}
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

        $headers = [
            $this->translator->trans('stock_movement.export.date'),
            $this->translator->trans('stock_movement.export.product'),
            $this->translator->trans('stock_movement.export.warehouse'),
            $this->translator->trans('stock_movement.export.quantity'),
            $this->translator->trans('stock_movement.export.type'),
            $this->translator->trans('stock_movement.export.user'),
            $this->translator->trans('stock_movement.export.comment'),
        ];
        $naLabel = $this->translator->trans('common.na');
        $systemLabel = $this->translator->trans('stock_movement.export.system');

        $response = new StreamedResponse(function () use ($movements, $headers, $naLabel, $systemLabel) {
            $handle = fopen('php://output', 'w+');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $headers, ';');

            foreach ($movements as $movement) {
                fputcsv($handle, [
                    $movement->getCreatedAt()?->format('d/m/Y H:i:s') ?? '',
                    $movement->getProduct()?->getName() ?? $naLabel,
                    $movement->getWarehouse()?->getName() ?? $naLabel,
                    $movement->getQuantity(),
                    $movement->getType(),
                    $movement->getUser()?->getName() ?? $systemLabel,
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

        $title = $this->translator->trans('stock_movement.export.title');
        $exportDate = $this->translator->trans('stock_movement.export.export_date');
        $totalLabel = $this->translator->trans('stock_movement.export.total');
        $movementsLabel = $this->translator->trans('stock_movement.export.movements');
        $naLabel = $this->translator->trans('common.na');
        $systemLabel = $this->translator->trans('stock_movement.export.system');

        $headers = [
            $this->translator->trans('stock_movement.export.date'),
            $this->translator->trans('stock_movement.export.product'),
            $this->translator->trans('stock_movement.export.warehouse'),
            $this->translator->trans('stock_movement.export.quantity'),
            $this->translator->trans('stock_movement.export.type'),
            $this->translator->trans('stock_movement.export.user'),
            $this->translator->trans('stock_movement.export.comment'),
        ];

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
            <h1>' . htmlspecialchars($title) . '</h1>
            <p>' . htmlspecialchars($exportDate) . ': ' . date('d/m/Y H:i:s') . '</p>
            <table>
                <thead>
                    <tr>
                        <th>' . htmlspecialchars($headers[0]) . '</th>
                        <th>' . htmlspecialchars($headers[1]) . '</th>
                        <th>' . htmlspecialchars($headers[2]) . '</th>
                        <th>' . htmlspecialchars($headers[3]) . '</th>
                        <th>' . htmlspecialchars($headers[4]) . '</th>
                        <th>' . htmlspecialchars($headers[5]) . '</th>
                        <th>' . htmlspecialchars($headers[6]) . '</th>
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
                <td>' . htmlspecialchars($movement->getProduct()?->getName() ?? $naLabel) . '</td>
                <td>' . htmlspecialchars($movement->getWarehouse()?->getName() ?? $naLabel) . '</td>
                <td><span class="' . $qtyClass . '">' . $qtyDisplay . '</span></td>
                <td><span class="' . $typeClass . '">' . $type . '</span></td>
                <td>' . htmlspecialchars($movement->getUser()?->getName() ?? $systemLabel) . '</td>
                <td>' . htmlspecialchars($movement->getComment() ?? '-') . '</td>
            </tr>';
        }

        $html .= '</tbody></table>
            <div class="footer">' . htmlspecialchars($totalLabel) . ': ' . count($movements) . ' ' . htmlspecialchars($movementsLabel) . '</div>
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
