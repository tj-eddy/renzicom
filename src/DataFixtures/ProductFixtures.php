<?php


// src/DataFixtures/ProductFixtures.php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductImage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $productNames = [
            'Harry Potter et la Pierre Philosophale',
            'Le Seigneur des Anneaux',
            'Game of Thrones - Volume 1',
            '1984',
            'L\'Alchimiste',
            'Le Petit Prince',
            'Pride and Prejudice',
            'To Kill a Mockingbird',
            'The Great Gatsby',
            'Moby Dick',
            'Don Quichotte',
            'Les Misérables',
            'Guerre et Paix',
            'L\'Odyssée',
            'Crime et Châtiment'
        ];

        $languages = ['Français', 'Anglais', 'Espagnol', 'Allemand', 'Italien'];

        $formats = ['Broché', 'Relié', 'eBook', 'Audio', 'Poche', 'Grand Format'];
        $conditions = ['Neuf', 'Comme Neuf', 'Très Bon État', 'Bon État'];
        $editions = ['Édition Standard', 'Édition Collector', 'Édition Limitée', 'Édition Deluxe'];

        for ($i = 0; $i < 50; $i++) {
            $product = new Product();
            $product->setName($faker->randomElement($productNames) . ' - Édition ' . $faker->year());
            $product->setYearEdition($faker->numberBetween(1990, 2024));
            $product->setLanguage($faker->randomElement($languages));

            // Variant en format string séparé par des virgules
            $variantParts = [
                $faker->randomElement($formats),
                $faker->randomElement($conditions),
                $faker->randomElement($editions),
                $faker->numberBetween(100, 1000) . ' pages'
            ];
            $product->setVariant(implode(', ', $variantParts));

            $createdAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year'));
            $product->setCreatedAt($createdAt);

            $manager->persist($product);

            // ✅ Créer 1 à 3 images avec des noms de fichiers simples
            $imageCount = rand(1, 3);
            for ($img = 0; $img < $imageCount; $img++) {
                $productImage = new ProductImage();

                // ✅ Nom de fichier simple (sera préfixé automatiquement par "uploads/products/")
                $productImage->setFilename("product-{$i}-{$img}.jpg");

                $productImage->setProduct($product);

                $uploadedAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months'));
                $productImage->setUploadedAt($uploadedAt);

                $manager->persist($productImage);
            }

            $this->addReference('product_' . $i, $product);
        }

        $manager->flush();
    }
}
