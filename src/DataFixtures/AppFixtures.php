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

        $warehouseData = [
            [
                'name' => 'Entrepôt Paris Nord',
                'address' => '123 Avenue de la République, 93200 Saint-Denis',
            ],
            [
                'name' => 'Entrepôt Lyon Centre',
                'address' => '45 Rue de la Part-Dieu, 69003 Lyon',
            ],
            [
                'name' => 'Entrepôt Marseille Sud',
                'address' => '78 Boulevard National, 13003 Marseille',
            ],
            [
                'name' => 'Entrepôt Bordeaux',
                'address' => '12 Quai des Chartrons, 33000 Bordeaux',
            ],
            [
                'name' => 'Entrepôt Toulouse',
                'address' => '56 Avenue de Muret, 31300 Toulouse',
            ],
        ];

        foreach ($warehouseData as $data) {
            $warehouse = new Warehouse();
            $warehouse->setName($data['name']);
            $warehouse->setAddress($data['address']);
            $warehouse->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($warehouse);
            $warehouses[] = $warehouse;
        }

        echo "✅ " . count($warehouses) . " entrepôts créés\n";

        // ========================================
        // CRÉATION DES PRODUITS (MAGAZINES)
        // ========================================

        $products = [];

        $productData = [
            // Magazines d'actualité
            [
                'name' => 'Paris Match',
                'image' => 'paris-match.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'hebdomadaire', 'format' => 'A4'],
            ],
            [
                'name' => 'L\'Express',
                'image' => 'express.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'hebdomadaire', 'format' => 'A4'],
            ],
            [
                'name' => 'Le Point',
                'image' => 'le-point.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'hebdomadaire', 'format' => 'A4'],
            ],
            [
                'name' => 'L\'Obs',
                'image' => 'obs.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'hebdomadaire', 'format' => 'A4'],
            ],

            // Magazines féminins
            [
                'name' => 'Elle',
                'image' => 'elle.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'hebdomadaire', 'format' => 'A4', 'catégorie' => 'féminin'],
            ],
            [
                'name' => 'Marie Claire',
                'image' => 'marie-claire.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'mensuel', 'format' => 'A4', 'catégorie' => 'féminin'],
            ],
            [
                'name' => 'Femme Actuelle',
                'image' => 'femme-actuelle.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'hebdomadaire', 'format' => 'A4', 'catégorie' => 'féminin'],
            ],

            // Magazines lifestyle
            [
                'name' => 'Geo',
                'image' => 'geo.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'mensuel', 'format' => 'A4', 'catégorie' => 'voyage'],
            ],
            [
                'name' => 'National Geographic',
                'image' => 'natgeo.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'mensuel', 'format' => 'A4', 'catégorie' => 'découverte'],
            ],
            [
                'name' => 'Cuisine et Vins de France',
                'image' => 'cuisine-vins.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'mensuel', 'format' => 'A4', 'catégorie' => 'gastronomie'],
            ],

            // Magazines économiques
            [
                'name' => 'Challenges',
                'image' => 'challenges.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'hebdomadaire', 'format' => 'A4', 'catégorie' => 'économie'],
            ],
            [
                'name' => 'Capital',
                'image' => 'capital.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'mensuel', 'format' => 'A4', 'catégorie' => 'économie'],
            ],

            // Magazines sportifs
            [
                'name' => 'L\'Équipe Magazine',
                'image' => 'equipe-mag.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'hebdomadaire', 'format' => 'A4', 'catégorie' => 'sport'],
            ],
            [
                'name' => 'France Football',
                'image' => 'france-football.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'hebdomadaire', 'format' => 'A4', 'catégorie' => 'sport'],
            ],

            // Magazines tech
            [
                'name' => 'Science et Vie',
                'image' => 'science-vie.jpg',
                'year_edition' => 2024,
                'language' => 'FR',
                'variant' => ['type' => 'mensuel', 'format' => 'A4', 'catégorie' => 'science'],
            ],

            // Magazines internationaux
            [
                'name' => 'Time Magazine',
                'image' => 'time.jpg',
                'year_edition' => 2024,
                'language' => 'EN',
                'variant' => ['type' => 'hebdomadaire', 'format' => 'A4', 'catégorie' => 'actualité'],
            ],
            [
                'name' => 'The Economist',
                'image' => 'economist.jpg',
                'year_edition' => 2024,
                'language' => 'EN',
                'variant' => ['type' => 'hebdomadaire', 'format' => 'A4', 'catégorie' => 'économie'],
            ],
            [
                'name' => 'Vogue',
                'image' => 'vogue.jpg',
                'year_edition' => 2024,
                'language' => 'EN',
                'variant' => ['type' => 'mensuel', 'format' => 'A4', 'catégorie' => 'mode'],
            ],

            // Magazines allemands
            [
                'name' => 'Der Spiegel',
                'image' => 'spiegel.jpg',
                'year_edition' => 2024,
                'language' => 'DE',
                'variant' => ['type' => 'hebdomadaire', 'format' => 'A4', 'catégorie' => 'actualité'],
            ],
            [
                'name' => 'Stern',
                'image' => 'stern.jpg',
                'year_edition' => 2024,
                'language' => 'DE',
                'variant' => ['type' => 'hebdomadaire', 'format' => 'A4', 'catégorie' => 'actualité'],
            ],
        ];

        foreach ($productData as $data) {
            $product = new Product();
            $product->setName($data['name']);

            // Vérifier si l'image existe, sinon utiliser null
            $imagePath = __DIR__ . '/../../public/uploads/products/' . $data['image'];
            if (file_exists($imagePath)) {
                $product->setImage($data['image']);
            } else {
                $product->setImage(null);
            }

            $product->setYearEdition($data['year_edition']);
            $product->setLanguage($data['language']);
            $product->setVariant($data['variant']);
            $product->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($product);
            $products[] = $product;
        }

        echo "✅ " . count($products) . " produits créés\n";

        // ========================================
        // CRÉATION DES STOCKS
        // ========================================

        $stocks = [];
        $totalStockQuantity = 0;

        // Créer des stocks pour chaque combinaison entrepôt/produit
        foreach ($warehouses as $warehouse) {
            // Chaque entrepôt a environ 60-80% des produits en stock
            $productsToStock = $this->getRandomElements($products, rand(12, 16));

            foreach ($productsToStock as $product) {
                $stock = new Stock();
                $stock->setWarehouse($warehouse);
                $stock->setProduct($product);

                // Quantités variées selon le type de produit
                $quantity = $this->getStockQuantity($product->getName());
                $stock->setQuantity($quantity);
                $totalStockQuantity += $quantity;

                // Notes aléatoires pour certains stocks
                if (rand(0, 100) < 30) { // 30% de chance d'avoir une note
                    $notes = [
                        'Stock minimum atteint',
                        'Réapprovisionnement prévu',
                        'Produit en promotion',
                        'Nouvelle édition disponible',
                        'Stock optimal',
                        'Attention: date limite proche',
                        'Bestseller',
                        'Stock de sécurité',
                    ];
                    $stock->setNote($notes[array_rand($notes)]);
                }

                $stock->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 30) . ' days'));

                $manager->persist($stock);
                $stocks[] = $stock;
            }
        }

        echo "✅ " . count($stocks) . " stocks créés\n";
        echo "📦 Quantité totale en stock: " . number_format($totalStockQuantity, 0, ',', ' ') . " unités\n";

        // ========================================
        // CRÉATION DES HÔTELS
        // ========================================

        $hotels = [];

        $hotelData = [
            // Paris
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
            [
                'name' => 'Hôtel Le Bristol',
                'address' => '112 Rue du Faubourg Saint-Honoré, 75008 Paris',
                'contact_name' => 'Marie Leclerc',
                'contact_email' => 'marie.leclerc@lebristol.com',
                'contact_phone' => '+33 1 53 43 43 00',
            ],
            [
                'name' => 'Pullman Paris Tour Eiffel',
                'address' => '18 Avenue de Suffren, 75015 Paris',
                'contact_name' => 'Pierre Moreau',
                'contact_email' => 'pierre.moreau@pullman.com',
                'contact_phone' => '+33 1 44 38 56 00',
            ],

            // Lyon
            [
                'name' => 'Sofitel Lyon Bellecour',
                'address' => '20 Quai Gailleton, 69002 Lyon',
                'contact_name' => 'Isabelle Rousseau',
                'contact_email' => 'isabelle.rousseau@sofitel.com',
                'contact_phone' => '+33 4 72 41 20 20',
            ],
            [
                'name' => 'InterContinental Lyon',
                'address' => '20 Rue de la Bourse, 69002 Lyon',
                'contact_name' => 'Laurent Bernard',
                'contact_email' => 'laurent.bernard@intercontinental.com',
                'contact_phone' => '+33 4 72 77 93 00',
            ],

            // Marseille
            [
                'name' => 'InterContinental Marseille',
                'address' => '2 Boulevard Charles Livon, 13007 Marseille',
                'contact_name' => 'Nathalie Garcia',
                'contact_email' => 'nathalie.garcia@intercontinental.com',
                'contact_phone' => '+33 4 13 42 42 42',
            ],
            [
                'name' => 'NH Collection Marseille',
                'address' => '37 Boulevard des Dames, 13002 Marseille',
                'contact_name' => 'Marc Fabre',
                'contact_email' => 'marc.fabre@nh-hotels.com',
                'contact_phone' => '+33 4 91 14 91 91',
            ],

            // Bordeaux
            [
                'name' => 'InterContinental Bordeaux',
                'address' => '5 Place de la Comédie, 33000 Bordeaux',
                'contact_name' => 'Catherine Blanc',
                'contact_email' => 'catherine.blanc@intercontinental.com',
                'contact_phone' => '+33 5 57 30 44 44',
            ],
            [
                'name' => 'Radisson Blu Bordeaux',
                'address' => '4 Rue de la Devise, 33000 Bordeaux',
                'contact_name' => 'Thomas Girard',
                'contact_email' => 'thomas.girard@radisson.com',
                'contact_phone' => '+33 5 56 48 83 83',
            ],

            // Toulouse
            [
                'name' => 'Pullman Toulouse Centre',
                'address' => '84 Allées Jean Jaurès, 31000 Toulouse',
                'contact_name' => 'Sandrine Petit',
                'contact_email' => 'sandrine.petit@pullman.com',
                'contact_phone' => '+33 5 61 10 23 10',
            ],
            [
                'name' => 'Novotel Toulouse Centre',
                'address' => '5 Place du Capitole, 31000 Toulouse',
                'contact_name' => 'François Roux',
                'contact_email' => 'francois.roux@novotel.com',
                'contact_phone' => '+33 5 61 21 74 74',
            ],
        ];

        foreach ($hotelData as $data) {
            $hotel = new Hotel();
            $hotel->setName($data['name']);
            $hotel->setAddress($data['address']);
            $hotel->setContactName($data['contact_name']);
            $hotel->setContactEmail($data['contact_email']);
            $hotel->setContactPhone($data['contact_phone']);
            $hotel->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($hotel);
            $hotels[] = $hotel;
        }

        echo "✅ " . count($hotels) . " hôtels créés\n";

        // ========================================
        // CRÉATION DES PRÉSENTOIRS
        // ========================================

        $displays = [];

        $displayLocations = [
            'Hall d\'entrée',
            'Réception',
            'Lobby',
            'Salle petit-déjeuner',
            'Bar',
            'Salon',
            'Étage 1',
            'Étage 2',
            'Étage 3',
            'Espace affaires',
            'Salle de fitness',
            'Spa',
        ];

        // Créer 2-4 présentoirs par hôtel
        foreach ($hotels as $index => $hotel) {
            $numDisplays = rand(2, 4);

            for ($i = 0; $i < $numDisplays; $i++) {
                $display = new Display();
                $display->setName('Présentoir ' . chr(65 + $i)); // A, B, C, D
                $display->setLocation($displayLocations[array_rand($displayLocations)]);
                $display->setHotel($hotel);
                $display->setCreatedAt(new \DateTimeImmutable());

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

        // Créer 4-8 racks par présentoir
        foreach ($displays as $display) {
            $numRacks = rand(4, 8);

            for ($position = 1; $position <= $numRacks; $position++) {
                $rack = new Rack();
                $rack->setName('Rack ' . $position);
                $rack->setPosition($position);
                $rack->setDisplay($display);

                // 80% des racks ont un produit assigné
                if (rand(0, 100) < 80 && count($products) > 0) {
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

                $rack->setCreatedAt(new \DateTimeImmutable());

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
     * Obtenir des éléments aléatoires d'un tableau
     */
    private function getRandomElements(array $array, int $count): array
    {
        $keys = array_rand($array, min($count, count($array)));

        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $result = [];
        foreach ($keys as $key) {
            $result[] = $array[$key];
        }

        return $result;
    }

    /**
     * Déterminer la quantité de stock selon le produit
     */
    private function getStockQuantity(string $productName): int
    {
        // Produits populaires (magazines hebdomadaires français)
        $popularProducts = [
            'Paris Match', 'L\'Express', 'Le Point', 'Elle',
            'Femme Actuelle', 'L\'Équipe Magazine'
        ];

        // Produits moyens (mensuels et spécialisés)
        $mediumProducts = [
            'Geo', 'National Geographic', 'Marie Claire', 'Capital',
            'Challenges', 'Science et Vie'
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
            'Paris Match', 'L\'Express', 'Le Point', 'Elle',
            'Femme Actuelle', 'L\'Équipe Magazine'
        ];

        // Produits moyens
        $mediumProducts = [
            'Geo', 'National Geographic', 'Marie Claire', 'Capital',
            'Challenges', 'Science et Vie'
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
