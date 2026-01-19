<?php


// src/DataFixtures/WarehouseFixtures.php

namespace App\DataFixtures;

use App\Entity\Warehouse;
use App\Entity\WarehouseImage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class WarehouseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $warehouseNames = [
            'Entrepôt Central',
            'Entrepôt Nord',
            'Entrepôt Sud',
            'Entrepôt Est',
            'Entrepôt Ouest'
        ];

        foreach ($warehouseNames as $index => $name) {
            $warehouse = new Warehouse();
            $warehouse->setName($name);
            $warehouse->setAddress($faker->address());

            $manager->persist($warehouse);

            $imageCount = rand(1, 2);
            for ($img = 0; $img < $imageCount; $img++) {
                $warehouseImage = new WarehouseImage();

                // ✅ Nom de fichier simple (sera préfixé automatiquement)
                $warehouseImage->setImageName("warehouse-{$index}-{$img}.jpg");

                $warehouseImage->setImageSize($faker->numberBetween(100000, 500000));
                $warehouseImage->setWarehouse($warehouse);

                $createdAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 years'));
                $warehouseImage->setUploadedAt($createdAt);

                $manager->persist($warehouseImage);
            }

            $this->addReference('warehouse_' . $index, $warehouse);
        }

        $manager->flush();
    }
}
