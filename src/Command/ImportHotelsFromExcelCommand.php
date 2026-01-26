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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import:hotels-excel',
    description: 'Importe les hôtels, présentoirs et racks depuis le fichier Excel',
)]
class ImportHotelsFromExcelCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Importation des données depuis le fichier Excel');

        $filePath = __DIR__ . '/../../public/RC_Réseau-A_D_Présentoirs_Suisse.xlsx';

        if (!file_exists($filePath)) {
            $io->error("Le fichier Excel n'existe pas : $filePath");
            return Command::FAILURE;
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheetNames = $spreadsheet->getSheetNames();

            $io->info("Nombre de feuilles à traiter : " . count($sheetNames));

            $hotelCount = 0;
            $displayCount = 0;
            $rackCount = 0;
            $skippedCount = 0;

            // Parcourir chaque feuille
            foreach ($sheetNames as $sheetIndex => $sheetName) {
                $io->section("Traitement de la feuille : $sheetName");

                $worksheet = $spreadsheet->getSheet($sheetIndex);
                $highestRow = $worksheet->getHighestRow();

                $io->info("Nombre de lignes : $highestRow");

                // Parcourir les lignes (ligne 1 = en-tête)
                for ($row = 2; $row <= $highestRow; $row++) {
                    // Structure du fichier Excel:
                    // A=Date, B=Type, C=Nom établissement, D=Contact, E=Type présentoir,
                    // F=Adresse, G=N./Numéro, H=Code Postal, I=Lieu, J=Situation

                    $type = trim($worksheet->getCell("B$row")->getValue());
                    $hotelName = trim($worksheet->getCell("C$row")->getValue());
                    $contactName = trim($worksheet->getCell("D$row")->getValue());
                    $displayType = trim($worksheet->getCell("E$row")->getValue());
                    $street = trim($worksheet->getCell("F$row")->getValue());
                    $streetNumber = trim($worksheet->getCell("G$row")->getValue());
                    $postalCode = trim($worksheet->getCell("H$row")->getValue());
                    $city = trim($worksheet->getCell("I$row")->getValue());
                    $location = trim($worksheet->getCell("J$row")->getValue());

                    // Ignorer les lignes vides
                    if (empty($hotelName)) {
                        continue;
                    }

                    // Filtrer par type (Hotel, Auto, Automobile, etc.)
                    $typeNormalized = strtolower(trim($type));
                    if (!in_array($typeNormalized, ['hotel', 'auto', 'automobile'])) {
                        $skippedCount++;
                        continue;
                    }

                    // Construire l'adresse complète
                    $fullAddress = $street;
                    if (!empty($streetNumber)) {
                        $fullAddress .= ' ' . $streetNumber;
                    }
                    if (!empty($postalCode)) {
                        $fullAddress .= ', ' . $postalCode;
                    }
                    if (!empty($city)) {
                        $fullAddress .= ' ' . $city;
                    }

                    // Ajouter la région dans le nom pour différencier
                    $hotelFullName = $hotelName . " (" . $sheetName . ")";

                    // Créer l'hôtel/établissement
                    $hotel = new Hotel();
                    $hotel->setName($hotelFullName);
                    $hotel->setAddress($fullAddress ?: 'N/A');
                    $hotel->setContactName($contactName ?: null);
                    $hotel->setContactEmail(null); // Pas dans le fichier
                    $hotel->setContactPhone(null); // Pas dans le fichier

                    $this->entityManager->persist($hotel);
                    $hotelCount++;
                    $io->text("✓ Établissement créé : $hotelFullName");

                    // Créer un présentoir si le type est spécifié
                    if (!empty($displayType)) {
                        $display = new Display();
                        $display->setName($displayType);
                        $display->setLocation($location ?: 'Principal');
                        $display->setHotel($hotel);

                        $this->entityManager->persist($display);
                        $displayCount++;
                        $io->text("  ✓ Présentoir : $displayType");

                        // Créer quelques racks par défaut pour chaque présentoir
                        for ($i = 1; $i <= 3; $i++) {
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

                    // Flush tous les 20 lignes pour optimiser
                    if ($hotelCount % 20 === 0) {
                        $this->entityManager->flush();
                        $io->text("💾 Flush intermédiaire...");
                    }
                }

                $io->newLine();
            }

            // Flush final
            $this->entityManager->flush();

            $io->success([
                "Importation terminée avec succès !",
                "Établissements créés : $hotelCount",
                "Présentoirs créés : $displayCount",
                "Racks créés : $rackCount",
                "Lignes ignorées : $skippedCount",
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error("Erreur lors de l'importation : " . $e->getMessage());
            $io->error("Trace : " . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
