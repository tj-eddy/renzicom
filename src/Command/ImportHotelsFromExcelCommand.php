<?php

namespace App\Command;

use App\Entity\Display;
use App\Entity\Hotel;
use App\Entity\Product;
use App\Entity\Rack;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(
    name: 'app:import:hotels-excel',
    description: 'Importe les hôtels, présentoirs et racks depuis toutes les feuilles du fichier Excel',
)]
class ImportHotelsFromExcelCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'clear',
            'c',
            InputOption::VALUE_NONE,
            'Vider les tables hotel, display et rack avant l\'import'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Importation des données depuis le fichier Excel');

        // Option pour vider les tables
        if ($input->getOption('clear')) {
            $io->warning('⚠️  Vous êtes sur le point de supprimer TOUTES les données des tables : Hotel, Display, Rack');
            
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                'Êtes-vous sûr de vouloir continuer ? (oui/non) [non]: ',
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                $io->info('Opération annulée.');
                return Command::SUCCESS;
            }

            $io->section('🗑️  Suppression des données existantes...');
            
            try {
                // Désactiver temporairement les contraintes de clés étrangères
                $connection = $this->entityManager->getConnection();
                $platform = $connection->getDatabasePlatform();
                
                // Pour MySQL
                $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
                
                // Supprimer les données dans l'ordre (des enfants vers les parents)
                $rackCount = $this->entityManager->createQuery('DELETE FROM App\Entity\Rack')->execute();
                $io->text("  ✓ $rackCount racks supprimés");
                
                $displayCount = $this->entityManager->createQuery('DELETE FROM App\Entity\Display')->execute();
                $io->text("  ✓ $displayCount présentoirs supprimés");
                
                $hotelCount = $this->entityManager->createQuery('DELETE FROM App\Entity\Hotel')->execute();
                $io->text("  ✓ $hotelCount hôtels supprimés");
                
                // Réactiver les contraintes
                $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
                
                $io->success('Tables vidées avec succès !');
                $io->newLine();
                
            } catch (\Exception $e) {
                $io->error('Erreur lors de la suppression : ' . $e->getMessage());
                return Command::FAILURE;
            }
        }

        $filePath = __DIR__ . '/../../public/RC_Réseau-A_D_Présentoirs_Suisse.xlsx';

        if (!file_exists($filePath)) {
            $io->error("Le fichier Excel n'existe pas : $filePath");
            return Command::FAILURE;
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheetNames = $spreadsheet->getSheetNames();

            $io->info("📊 Nombre de feuilles trouvées : " . count($sheetNames));
            $io->listing($sheetNames);
            $io->newLine();

            // Compteurs globaux
            $totalHotels = 0;
            $totalDisplays = 0;
            $totalRacks = 0;
            $totalSkipped = 0;

            // Parcourir chaque feuille (chaque région)
            foreach ($sheetNames as $sheetIndex => $sheetName) {
                $io->section("📍 Traitement de la région : $sheetName");

                $worksheet = $spreadsheet->getSheet($sheetIndex);
                $highestRow = $worksheet->getHighestRow();

                $io->info("Nombre de lignes dans cette feuille : " . ($highestRow - 1));

                $hotelCount = 0;
                $displayCount = 0;
                $rackCount = 0;
                $skippedCount = 0;

                // Parcourir les lignes (ligne 1 = en-tête, donc on commence à 2)
                for ($row = 2; $row <= $highestRow; $row++) {
                    // Lecture des colonnes
                    $date = $worksheet->getCell("A$row")->getValue();
                    $type = trim($worksheet->getCell("B$row")->getValue() ?? '');
                    $hotelName = trim($worksheet->getCell("C$row")->getValue() ?? '');
                    $contactName = trim($worksheet->getCell("D$row")->getValue() ?? '');
                    $displayType = trim($worksheet->getCell("E$row")->getValue() ?? '');
                    $street = trim($worksheet->getCell("F$row")->getValue() ?? '');
                    $streetNumber = trim($worksheet->getCell("G$row")->getValue() ?? '');
                    $postalCode = trim($worksheet->getCell("H$row")->getValue() ?? '');
                    $city = trim($worksheet->getCell("I$row")->getValue() ?? '');
                    $reopeningDate = $worksheet->getCell("J$row")->getValue();

                    // Ignorer les lignes vides
                    if (empty($hotelName)) {
                        $skippedCount++;
                        continue;
                    }

                    // Ignorer les lignes "ARRETE"
                    if (strtoupper(trim($reopeningDate ?? '')) === 'ARRETE') {
                        $io->text("  ⊗ Ignoré (ARRETE) : $hotelName");
                        $skippedCount++;
                        continue;
                    }

                    // Construire l'adresse complète
                    $fullAddress = '';
                    if (!empty($street)) {
                        $fullAddress = $street;
                        if (!empty($streetNumber)) {
                            $fullAddress .= ' ' . $streetNumber;
                        }
                    }
                    if (!empty($postalCode) || !empty($city)) {
                        if (!empty($fullAddress)) {
                            $fullAddress .= ', ';
                        }
                        if (!empty($postalCode)) {
                            $fullAddress .= $postalCode . ' ';
                        }
                        if (!empty($city)) {
                            $fullAddress .= $city;
                        }
                    }

                    // Normaliser le type pour avoir une casse correcte
                    $typePrefix = $this->normalizeType($type);

                    // Construire le nom complet avec préfixe type + nom + région
                    // Format: "[Type] Nom (Région)"
                    $hotelFullName = "[$typePrefix] $hotelName ($sheetName)";

                    // Vérifier si l'hôtel existe déjà
                    $existingHotel = $this->entityManager->getRepository(Hotel::class)
                        ->findOneBy(['name' => $hotelFullName]);

                    if ($existingHotel) {
                        $hotel = $existingHotel;
                        $io->text("  ↻ Hôtel existant : $hotelFullName");
                    } else {
                        // Créer l'hôtel/établissement
                        $hotel = new Hotel();
                        $hotel->setName($hotelFullName);
                        $hotel->setAddress($fullAddress ?: null);
                        $hotel->setContactName($contactName ?: null);
                        $hotel->setContactEmail(null);
                        $hotel->setContactPhone(null);

                        $this->entityManager->persist($hotel);
                        $hotelCount++;
                        $io->text("  ✓ Hôtel créé : $hotelFullName");
                    }

                    // Créer un présentoir si le type est spécifié
                    if (!empty($displayType)) {
                        // Construire la localisation
                        $locationDetails = [];
                        if (!empty($street)) {
                            $locationDetails[] = $street . (!empty($streetNumber) ? ' ' . $streetNumber : '');
                        }
                        if (!empty($city)) {
                            $locationDetails[] = $city;
                        }
                        $locationString = !empty($locationDetails) ? implode(', ', $locationDetails) : 'Principal';

                        // Vérifier si le display existe déjà
                        $existingDisplay = $this->entityManager->getRepository(Display::class)
                            ->findOneBy([
                                'hotel' => $hotel,
                                'name' => $displayType
                            ]);

                        if (!$existingDisplay) {
                            $display = new Display();
                            $display->setName($displayType);
                            $display->setLocation($locationString);
                            $display->setHotel($hotel);

                            $this->entityManager->persist($display);
                            $displayCount++;
                            $io->text("    ✓ Présentoir : $displayType");

                            // Créer des racks selon le type
                            $numberOfRacks = $this->getNumberOfRacksByType($displayType);
                            
                            for ($i = 1; $i <= $numberOfRacks; $i++) {
                                $rack = new Rack();
                                $rack->setName("Rack $i");
                                $rack->setPosition($i - 1);
                                $rack->setRequiredQuantity(10);
                                $rack->setCurrentQuantity(0);
                                $rack->setDisplay($display);

                                $this->entityManager->persist($rack);
                                $rackCount++;
                            }
                        }
                    }

                    // Flush tous les 50 lignes
                    if (($hotelCount + $displayCount) % 50 === 0) {
                        $this->entityManager->flush();
                        $io->text("    💾 Flush intermédiaire...");
                    }
                }

                // Flush après chaque feuille
                $this->entityManager->flush();

                // Statistiques pour cette feuille
                $io->info([
                    "Feuille '$sheetName' terminée:",
                    "  → Hôtels créés : $hotelCount",
                    "  → Présentoirs créés : $displayCount",
                    "  → Racks créés : $rackCount",
                    "  → Lignes ignorées : $skippedCount",
                ]);
                $io->newLine();

                // Ajouter aux totaux
                $totalHotels += $hotelCount;
                $totalDisplays += $displayCount;
                $totalRacks += $rackCount;
                $totalSkipped += $skippedCount;
            }

            // Flush final
            $this->entityManager->flush();
            $this->entityManager->clear();

            // Statistiques globales
            $io->success([
                "🎉 Importation de toutes les feuilles terminée !",
                "═══════════════════════════════════════════",
                "📊 TOTAUX GLOBAUX :",
                "  → Feuilles traitées : " . count($sheetNames),
                "  → Hôtels créés : $totalHotels",
                "  → Présentoirs créés : $totalDisplays",
                "  → Racks créés : $totalRacks",
                "  → Lignes ignorées : $totalSkipped",
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error("Erreur lors de l'importation : " . $e->getMessage());
            $io->error("Trace : " . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Normalise le type d'établissement pour avoir une casse cohérente
     */
    private function normalizeType(string $type): string
    {
        $type = trim($type);
        
        if (empty($type)) {
            return 'Autre';
        }

        // Capitaliser la première lettre
        $normalized = ucfirst(strtolower($type));
        
        // Gérer les cas spéciaux
        $typesMap = [
            'hotel' => 'Hotel',
            'hôtel' => 'Hotel',
            'auto' => 'Auto',
            'automobile' => 'Auto',
            'moto' => 'Moto',
            'sport' => 'Sport',
            'restaurant' => 'Restaurant',
            'business' => 'Business',
        ];
        
        $lowerType = strtolower($type);
        
        return $typesMap[$lowerType] ?? $normalized;
    }

    /**
     * Détermine le nombre de racks selon le type de présentoir
     */
    private function getNumberOfRacksByType(string $displayType): int
    {
        $type = strtolower($displayType);
        
        if (str_contains($type, 'grand')) {
            return 5; // Présentoirs grands = 5 racks
        } elseif (str_contains($type, 'petit')) {
            return 3; // Présentoirs petits = 3 racks
        }
        
        return 4; // Par défaut
    }
}