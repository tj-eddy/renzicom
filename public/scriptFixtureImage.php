<?php

// scripts/download_images.php

// Créer les dossiers
@mkdir(__DIR__.'/../public/uploads/products', 0777, true);
@mkdir(__DIR__.'/../public/uploads/warehouses', 0777, true);

echo "📥 Téléchargement des images de produits...\n";

// Télécharger images de produits
for ($i = 0; $i < 50; ++$i) {
    for ($img = 0; $img < 3; ++$img) {
        $url = "https://picsum.photos/400/600?random={$i}{$img}";
        $filepath = __DIR__."/../public/uploads/products/product-{$i}-{$img}.jpg";

        $imageData = file_get_contents($url);
        if (false !== $imageData) {
            file_put_contents($filepath, $imageData);
            echo "✓ product-{$i}-{$img}.jpg\n";
        }
    }
}

echo "📥 Téléchargement des images d'entrepôts...\n";

// Télécharger images d'entrepôts
for ($i = 0; $i < 5; ++$i) {
    for ($img = 0; $img < 2; ++$img) {
        $url = "https://picsum.photos/800/600?random=warehouse{$i}{$img}";
        $filepath = __DIR__."/../public/uploads/warehouses/warehouse-{$i}-{$img}.jpg";

        $imageData = file_get_contents($url);
        if (false !== $imageData) {
            file_put_contents($filepath, $imageData);
            echo "✓ warehouse-{$i}-{$img}.jpg\n";
        }
    }
}

echo "✅ Toutes les images ont été téléchargées !\n";
