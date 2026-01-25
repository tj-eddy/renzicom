<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Form\HotelType;
use App\Repository\HotelRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/hotel')]
class HotelController extends AbstractController
{
    #[Route('/', name: 'app_hotel_index', methods: ['GET'])]
    public function index(HotelRepository $hotelRepository): Response
    {
        return $this->render('hotel/index.html.twig', [
            'hotels' => $hotelRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_hotel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository): Response
    {
        $hotel = new Hotel();
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // La date de création de l'hôtel est gérée par @PrePersist

            // Traiter les displays et leurs racks
            foreach ($hotel->getDisplays() as $display) {
                $display->setHotel($hotel);
                // La date de création du display est gérée par @PrePersist

                // Traiter les racks de ce display
                foreach ($display->getRacks() as $rack) {
                    $rack->setDisplay($display);
                    // La date de création du rack est gérée par @PrePersist
                    // Initialiser currentQuantity à 0 si pas défini
                    if ($rack->getCurrentQuantity() === null) {
                        $rack->setCurrentQuantity(0);
                    }
                    $entityManager->persist($rack);
                }

                $entityManager->persist($display);
            }

            $entityManager->persist($hotel);
            $entityManager->flush();

            $this->addFlash('success', 'L\'hôtel a été créé avec succès avec ses présentoirs et racks.');

            return $this->redirectToRoute('app_hotel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hotel/new.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_hotel_show', methods: ['GET'])]
    public function show(Hotel $hotel): Response
    {
        return $this->render('hotel/show.html.twig', [
            'hotel' => $hotel,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_hotel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hotel $hotel, EntityManagerInterface $entityManager, ProductRepository $productRepository): Response
    {
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traiter les displays
            foreach ($hotel->getDisplays() as $display) {
                if (!$display->getId()) {
                    $display->setHotel($hotel);
                }

                // Traiter les racks
                foreach ($display->getRacks() as $rack) {
                    if (!$rack->getId()) {
                        $rack->setDisplay($display);
                        if ($rack->getCurrentQuantity() === null) {
                            $rack->setCurrentQuantity(0);
                        }
                    }
                    $entityManager->persist($rack);
                }

                $entityManager->persist($display);
            }

            $entityManager->flush();

            $this->addFlash('success', 'L\'hôtel a été mis à jour avec succès.');

            return $this->redirectToRoute('app_hotel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hotel/edit.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_hotel_delete', methods: ['POST'])]
    public function delete(Request $request, Hotel $hotel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hotel->getId(), $request->request->get('_token'))) {
            $entityManager->remove($hotel);
            $entityManager->flush();

            $this->addFlash('success', 'L\'hôtel a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_hotel_index', [], Response::HTTP_SEE_OTHER);
    }
}
