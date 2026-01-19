<?php
// src/DataFixtures/RackFixtures.php

namespace App\DataFixtures;

use App\Entity\Rack;
use App\Entity\Warehouse;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RackFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $rackCounter = 0;

        for ($w = 0; $w < 5; $w++) {
            /** @var Warehouse $warehouse */
            $warehouse = $this->getReference('warehouse_' . $w, Warehouse::class); // ✅ Ajouter la classe
            $rackCount = rand(5, 10);

            for ($r = 1; $r <= $rackCount; $r++) {
                $rack = new Rack();
                $rack->setName("Rack {$r} - Zone " . chr(65 + rand(0, 3)));
                $rack->setAddress("Allée " . rand(1, 10) . ", Position " . rand(1, 20));
                $rack->setWarehouse($warehouse);

                $manager->persist($rack);
                $this->addReference('rack_' . $rackCounter, $rack);
                $rackCounter++;
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [WarehouseFixtures::class];
    }
}
