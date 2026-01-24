<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Form\HotelType;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/hotel')]
class HotelController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/', name: 'app_hotel_index', methods: ['GET'])]
    public function index(HotelRepository $hotelRepository): Response
    {
        return $this->render('hotel/index.html.twig', [
            'hotels' => $hotelRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_hotel_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $hotel = new Hotel();
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($hotel);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('hotel.created'));

            return $this->redirectToRoute('app_hotel_index');
        }

        return $this->render('hotel/new.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_hotel_show', methods: ['GET'])]
    public function show(Hotel $hotel): JsonResponse
    {
        $data = [
            'id' => $hotel->getId(),
            'name' => $hotel->getName(),
            'address' => $hotel->getAddress(),
            'contact' => [
                'name' => $hotel->getContactName(),
                'email' => $hotel->getContactEmail(),
                'phone' => $hotel->getContactPhone(),
            ],
            'displays' => [],
        ];

        foreach ($hotel->getDisplays() as $display) {
            $displayData = [
                'id' => $display->getId(),
                'name' => $display->getName(),
                'location' => $display->getLocation(),
                'racks' => [],
            ];

            foreach ($display->getRacks() as $rack) {
                $product = $rack->getProduct();

                $rackData = [
                    'id' => $rack->getId(),
                    'name' => $rack->getName(),
                    'position' => $rack->getPosition(),
                    'current_quantity' => $rack->getCurrentQuantity(),
                    'required_quantity' => $rack->getRequiredQuantity(),
                    'fill_percentage' => $rack->getRequiredQuantity() > 0
                        ? round(($rack->getCurrentQuantity() / $rack->getRequiredQuantity()) * 100)
                        : 0,
                    'status' => $this->getRackStatus($rack->getCurrentQuantity(), $rack->getRequiredQuantity()),
                    'product' => $product ? [
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'image' => $product->getImage(),
                        'language' => $product->getLanguage(),
                        'year_edition' => $product->getYearEdition(),
                    ] : null,
                ];

                $displayData['racks'][] = $rackData;
            }

            // Trier les racks par position
            usort($displayData['racks'], fn($a, $b) => $a['position'] <=> $b['position']);

            $data['displays'][] = $displayData;
        }

        return $this->json($data);
    }

    private function getRackStatus(int $current, int $required): string
    {
        if (0 === $required) {
            return 'empty';
        }

        $percentage = ($current / $required) * 100;

        if ($percentage >= 100) {
            return 'full';
        }
        if ($percentage >= 50) {
            return 'medium';
        }
        if ($percentage > 0) {
            return 'low';
        }

        return 'empty';
    }

    #[Route('/{id}/edit', name: 'app_hotel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hotel $hotel): Response
    {
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('hotel.updated'));

            return $this->redirectToRoute('app_hotel_index');
        }

        return $this->render('hotel/edit.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hotel_delete', methods: ['POST'])]
    public function delete(Request $request, Hotel $hotel): Response
    {
        if ($this->isCsrfTokenValid('delete' . $hotel->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($hotel);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('hotel.deleted'));
        } else {
            $this->addFlash('error', $this->translator->trans('exception.invalid_token'));
        }

        return $this->redirectToRoute('app_hotel_index');
    }
}
