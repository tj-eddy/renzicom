<?php
// src/DataFixtures/StockFixtures.php

namespace App\DataFixtures;

use App\Entity\Stock;
use App\Entity\Product;
use App\Entity\Rack;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class StockFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        // ✅ Calculer le nombre total de racks créés
        $totalRacks = 0;
        for ($w = 0; $w < 5; $w++) {
            $rackCount = rand(5, 10);
            $totalRacks += $rackCount;
        }
        // Pour être sûr, on fixe à un maximum connu
        // Ou on récupère depuis RackFixtures

        for ($i = 0; $i < 50; $i++) {
            $stockCount = rand(1, 4);

            for ($s = 0; $s < $stockCount; $s++) {
                $stock = new Stock();
                $stock->setQuantity($faker->numberBetween(0, 500));

                /** @var Product $product */
                $product = $this->getReference('product_' . $i, Product::class);
                $stock->setProduct($product);

                // ✅ Utiliser un rack aléatoire parmi ceux créés
                // On sait qu'on a entre 25 et 50 racks (5 entrepôts × 5-10 racks)
                // Solution : Créer une liste des racks disponibles
                $rackIndex = rand(0, 24); // Minimum garanti : 5 entrepôts × 5 racks = 25

                try {
                    /** @var Rack $rack */
                    $rack = $this->getReference('rack_' . $rackIndex, Rack::class);
                    $stock->setRack($rack);
                } catch (\OutOfBoundsException $e) {
                    // Si le rack n'existe pas, on prend le premier disponible
                    $rack = $this->getReference('rack_0', Rack::class);
                    $stock->setRack($rack);
                }

                $createdAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months'));
                $stock->setCreateAt($createdAt);

                $stock->setNote($faker->optional(0.3)->sentence());

                $manager->persist($stock);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
            RackFixtures::class
        ];
    }
}
