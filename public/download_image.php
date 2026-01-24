<?php

// scripts/download_magazine_images.php

// Créer le dossier pour les images de produits
@mkdir(__DIR__.'/../public/uploads/products', 0777, true);

echo "📰 Téléchargement des images de magazines...\n\n";

// Mapping des magazines avec des images appropriées
$magazineImages = [
    // Magazines français d'actualité
    'paris-match.jpg' => 'https://picsum.photos/400/600?random=news1',
    'express.jpg' => 'https://picsum.photos/400/600?random=news2',
    'le-point.jpg' => 'https://picsum.photos/400/600?random=news3',
    'obs.jpg' => 'https://picsum.photos/400/600?random=news4',

    // Magazines féminins
    'elle.jpg' => 'https://picsum.photos/400/600?random=fashion1',
    'marie-claire.jpg' => 'https://picsum.photos/400/600?random=fashion2',
    'femme-actuelle.jpg' => 'https://picsum.photos/400/600?random=fashion3',

    // Magazines lifestyle
    'geo.jpg' => 'https://picsum.photos/400/600?random=travel1',
    'natgeo.jpg' => 'https://picsum.photos/400/600?random=nature1',
    'cuisine-vins.jpg' => 'https://picsum.photos/400/600?random=food1',

    // Magazines économiques
    'challenges.jpg' => 'https://picsum.photos/400/600?random=business1',
    'capital.jpg' => 'https://picsum.photos/400/600?random=business2',

    // Magazines sportifs
    'equipe-mag.jpg' => 'https://picsum.photos/400/600?random=sport1',
    'france-football.jpg' => 'https://picsum.photos/400/600?random=sport2',

    // Magazines tech/science
    'science-vie.jpg' => 'https://picsum.photos/400/600?random=tech1',

    // Magazines internationaux
    'time.jpg' => 'https://picsum.photos/400/600?random=international1',
    'economist.jpg' => 'https://picsum.photos/400/600?random=international2',
    'vogue.jpg' => 'https://picsum.photos/400/600?random=fashion4',

    // Magazines allemands
    'spiegel.jpg' => 'https://picsum.photos/400/600?random=german1',
    'stern.jpg' => 'https://picsum.photos/400/600?random=german2',
];

$successCount = 0;
$errorCount = 0;

foreach ($magazineImages as $filename => $url) {
    $filepath = __DIR__."/../public/uploads/products/{$filename}";

    echo "📥 Téléchargement de {$filename}... ";

    // Ajouter un petit délai pour éviter de surcharger le serveur
    usleep(500000); // 0.5 seconde

    try {
        $imageData = @file_get_contents($url);

        if (false !== $imageData && !empty($imageData)) {
            file_put_contents($filepath, $imageData);
            echo "✅\n";
            $successCount++;
        } else {
            echo "❌ Erreur de téléchargement\n";
            $errorCount++;
        }
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════\n";
echo "📊 Résumé du téléchargement:\n";
echo "   ✅ Succès: {$successCount} images\n";
echo "   ❌ Erreurs: {$errorCount} images\n";
echo "   📁 Dossier: public/uploads/products/\n";
echo "═══════════════════════════════════════════════════\n";

if ($successCount > 0) {
    echo "✨ Images téléchargées avec succès !\n";
} else {
    echo "⚠️  Aucune image téléchargée. Vérifiez votre connexion internet.\n";
}
