<?php

namespace App\DataFixtures;

use App\Entity\Warehouse;
use App\Entity\Product;
use App\Entity\Stock;
use App\Entity\Hotel;
use App\Entity\Display;
use App\Entity\Rack;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ========================================
        // CRÉATION DES ENTREPÔTS
        // ========================================

        $warehouses = [];
        $warehouseNames = [
            'Entrepôt Central',
            'Dépôt Nord',
            'Logistique Sud',
            'Hub Est',
            'Base Ouest',
            'Stock de Paris',
            'Dépôt Lyon',
            'Magasin Marseille',
            'Hub Bordeaux',
            'Lille Logistique',
            'Nantes Dépôt',
            'Strasbourg Hub',
            'Montpellier Stock',
            'Toulouse Base',
            'Nice Logistique',
            'Rennes Hub',
            'Reims Dépôt',
            'Le Havre Stock',
            'Saint-Étienne Base',
            'Toulon Logistique'
        ];

        for ($i = 0; $i < 20; $i++) {
            $warehouse = new Warehouse();
            $warehouse->setName($warehouseNames[$i] ?? 'Entrepôt #' . ($i + 1));
            $warehouse->setAddress(($i + 100) . ' Rue de la Logistique, France');
            $manager->persist($warehouse);
            $warehouses[] = $warehouse;
        }

        echo "✅ 20 entrepôts créés\n";

        // ========================================
        // CRÉATION DES PRODUITS (MAGAZINES)
        // ========================================

        $products = [];
        $magazines = ['Paris Match', 'Elle', 'Geo', 'L\'Équipe Magazine', 'Time Magazine', 'Vogue', 'National Geographic', 'Psychologies', 'Capital', 'Le Point'];
        $languages = ['FR', 'EN', 'ES', 'DE', 'IT'];

        for ($i = 1; $i <= 500; $i++) {
            $baseName = $magazines[array_rand($magazines)];
            $product = new Product();
            $product->setName($baseName . ' #' . $i);
            $product->setYearEdition(rand(2020, 2026));
            $product->setLanguage($languages[array_rand($languages)]);
            $product->setVariant(['type' => 'publication', 'id' => $i]);
            $product->setImage(null);

            $manager->persist($product);
            $products[] = $product;
        }

        echo "✅ " . count($products) . " produits créés\n";

        // ========================================
        // CRÉATION DES STOCKS
        // ========================================

        $stocks = [];
        $totalStockQuantity = 0;

        // Distribuer les produits dans les entrepôts
        foreach ($products as $product) {
            // Un produit peut être présent dans 1 à 3 entrepôts
            $nbWarehouses = rand(1, 3);
            $availableWarehouses = $warehouses;
            shuffle($availableWarehouses);

            for ($j = 0; $j < $nbWarehouses; $j++) {
                $wh = $availableWarehouses[$j];
                $stock = new Stock();
                $stock->setWarehouse($wh);
                $stock->setProduct($product);

                $quantity = rand(10, 500);
                $stock->setQuantity($quantity);
                $totalStockQuantity += $quantity;

                $stock->setNote('Stock initial généré');
                $manager->persist($stock);
                $stocks[] = $stock;
            }
        }

        echo "✅ " . count($stocks) . " entrées de stock créées\n";
        echo "📦 Quantité totale en stock: " . number_format($totalStockQuantity, 0, ',', ' ') . " unités\n";

        // ========================================
        // CRÉATION DES HÔTELS
        // ========================================

        $hotels = [];

        $hotelData = [
            [
                'name' => 'Hôtel Le Meurice',
                'address' => '228 Rue de Rivoli, 75001 Paris',
                'contact_name' => 'Sophie Martin',
                'contact_email' => 'sophie.martin@lemeurice.com',
                'contact_phone' => '+33 1 44 58 10 10',
            ],
            [
                'name' => 'Hôtel Plaza Athénée',
                'address' => '25 Avenue Montaigne, 75008 Paris',
                'contact_name' => 'Jean Dubois',
                'contact_email' => 'jean.dubois@plaza-athenee.com',
                'contact_phone' => '+33 1 53 67 66 65',
            ],
        ];

        foreach ($hotelData as $data) {
            $hotel = new Hotel();
            $hotel->setName($data['name']);
            $hotel->setAddress($data['address']);
            $hotel->setContactName($data['contact_name']);
            $hotel->setContactEmail($data['contact_email']);
            $hotel->setContactPhone($data['contact_phone']);

            $manager->persist($hotel);
            $hotels[] = $hotel;
        }

        echo "✅ " . count($hotels) . " hôtels créés\n";

        // ========================================
        // CRÉATION DES PRÉSENTOIRS
        // ========================================

        $displays = [];

        // Créer 2 présentoirs par hôtel
        foreach ($hotels as $hotel) {
            $locations = ['Hall d\'entrée', 'Réception'];

            for ($i = 0; $i < 2; $i++) {
                $display = new Display();
                $display->setName('Présentoir ' . chr(65 + $i)); // A, B
                $display->setLocation($locations[$i]);
                $display->setHotel($hotel);

                $manager->persist($display);
                $displays[] = $display;
            }
        }

        echo "✅ " . count($displays) . " présentoirs créés\n";

        // ========================================
        // CRÉATION DES RACKS
        // ========================================

        $racks = [];
        $totalRackCapacity = 0;
        $totalCurrentQuantity = 0;

        // Créer 4 racks par présentoir
        foreach ($displays as $display) {
            for ($position = 1; $position <= 4; $position++) {
                $rack = new Rack();
                $rack->setName('Rack ' . $position);
                $rack->setPosition($position);
                $rack->setDisplay($display);

                // Assigner un produit à chaque rack
                if (count($products) > 0) {
                    $product = $products[array_rand($products)];
                    $rack->setProduct($product);

                    // Quantité requise selon la popularité
                    $requiredQty = $this->getRackRequiredQuantity($product->getName());
                    $rack->setRequiredQuantity($requiredQty);

                    // Quantité actuelle: 40-95% de la quantité requise
                    $currentQty = rand((int)($requiredQty * 0.4), (int)($requiredQty * 0.95));
                    $rack->setCurrentQuantity($currentQty);

                    $totalRackCapacity += $requiredQty;
                    $totalCurrentQuantity += $currentQty;
                } else {
                    // Rack sans produit assigné
                    $rack->setProduct(null);
                    $rack->setRequiredQuantity(0);
                    $rack->setCurrentQuantity(0);
                }

                $manager->persist($rack);
                $racks[] = $rack;
            }
        }

        echo "✅ " . count($racks) . " racks créés\n";
        $fillRate = $totalRackCapacity > 0 ? round(($totalCurrentQuantity / $totalRackCapacity) * 100) : 0;
        echo "📊 Taux de remplissage des racks: {$fillRate}% ({$totalCurrentQuantity}/{$totalRackCapacity})\n";

        // ========================================
        // SAUVEGARDE EN BASE DE DONNÉES
        // ========================================

        $manager->flush();

        echo "\n🎉 Fixtures chargées avec succès !\n";
        echo "   - " . count($warehouses) . " entrepôts\n";
        echo "   - " . count($products) . " produits\n";
        echo "   - " . count($stocks) . " entrées de stock\n";
        echo "   - " . count($hotels) . " hôtels\n";
        echo "   - " . count($displays) . " présentoirs\n";
        echo "   - " . count($racks) . " racks\n";
    }

    /**
     * Déterminer la quantité de stock selon le produit
     */
    private function getStockQuantity(string $productName): int
    {
        // Produits populaires (magazines hebdomadaires français)
        $popularProducts = [
            'Paris Match',
            'Elle',
            'L\'Équipe Magazine'
        ];

        // Produits moyens (mensuels et spécialisés)
        $mediumProducts = [
            'Geo',
        ];

        if (in_array($productName, $popularProducts)) {
            // Stock élevé pour les produits populaires: 500-1500
            return rand(500, 1500);
        } elseif (in_array($productName, $mediumProducts)) {
            // Stock moyen: 200-800
            return rand(200, 800);
        } else {
            // Stock faible pour les produits de niche/internationaux: 50-300
            return rand(50, 300);
        }
    }

    /**
     * Déterminer la quantité requise pour un rack selon le produit
     */
    private function getRackRequiredQuantity(string $productName): int
    {
        // Produits populaires nécessitent plus d'exemplaires dans les racks
        $popularProducts = [
            'Paris Match',
            'Elle',
            'L\'Équipe Magazine'
        ];

        // Produits moyens
        $mediumProducts = [
            'Geo',
        ];

        if (in_array($productName, $popularProducts)) {
            // Racks pour produits populaires: 15-30 exemplaires
            return rand(15, 30);
        } elseif (in_array($productName, $mediumProducts)) {
            // Racks pour produits moyens: 8-20 exemplaires
            return rand(8, 20);
        } else {
            // Racks pour produits de niche: 5-12 exemplaires
            return rand(5, 12);
        }
    }
}
