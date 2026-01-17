<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LanguageController extends AbstractController
{
    #[Route('/change-locale/{locale}', name: 'app_change_locale', requirements: ['locale' => 'fr|en'])]
    public function changeLocale(string $locale, Request $request): Response
    {
        // Récupérer la route actuelle
        $route = $request->attributes->get('_route');
        $routeParams = $request->attributes->get('_route_params', []);

        // Changer la locale dans les paramètres
        $routeParams['_locale'] = $locale;

        // Rediriger vers la même page avec la nouvelle locale
        return $this->redirectToRoute($route, $routeParams);
    }
}
