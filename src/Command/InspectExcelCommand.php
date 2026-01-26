<?php

namespace App\Command;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:inspect:excel',
    description: 'Inspecte la structure du fichier Excel',
)]
class InspectExcelCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Inspection du fichier Excel');

        $filePath = __DIR__ . '/../../public/RC_Réseau-A_D_Présentoirs_Suisse.xlsx';

        if (!file_exists($filePath)) {
            $io->error("Le fichier Excel n'existe pas : $filePath");
            return Command::FAILURE;
        }

        try {
            $spreadsheet = IOFactory::load($filePath);

            // Afficher toutes les feuilles disponibles
            $sheetNames = $spreadsheet->getSheetNames();
            $io->section("Feuilles disponibles : " . count($sheetNames));
            foreach ($sheetNames as $index => $name) {
                $io->text("$index: $name");
            }
            $io->newLine();

            // Parcourir chaque feuille
            foreach ($sheetNames as $sheetIndex => $sheetName) {
                $io->section("=== Feuille $sheetIndex: $sheetName ===");

                $worksheet = $spreadsheet->getSheet($sheetIndex);
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();

                $io->info("Nombre de lignes : $highestRow");
                $io->info("Dernière colonne : $highestColumn");

                // Afficher les en-têtes (ligne 1)
                $io->writeln("En-têtes (ligne 1):");
                $headers = [];
                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $value = $worksheet->getCell($col . '1')->getValue();
                    $headers[$col] = $value;
                    $io->text("  $col: " . ($value ?: '(vide)'));
                }

                // Afficher les 3 premières lignes de données
                $io->writeln("\nAperçu des 3 premières lignes de données:");
                for ($row = 2; $row <= min(4, $highestRow); $row++) {
                    $io->writeln("--- Ligne $row ---");
                    for ($col = 'A'; $col <= $highestColumn; $col++) {
                        $value = $worksheet->getCell($col . $row)->getValue();
                        $header = $headers[$col] ?? $col;
                        $io->text("  $header: " . ($value ?: '(vide)'));
                    }
                    $io->newLine();
                }

                $io->newLine();
                $io->writeln(str_repeat('=', 80));
                $io->newLine();
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error("Erreur : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
