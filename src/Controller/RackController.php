<?php

namespace App\Controller;

use App\Entity\Rack;
use App\Form\RackType;
use App\Repository\RackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rack')]
final class RackController extends AbstractController
{
    #[Route(name: 'app_rack_index', methods: ['GET'])]
    public function index(RackRepository $rackRepository): Response
    {
        return $this->render('rack/index.html.twig', [
            'racks' => $rackRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_rack_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rack = new Rack();
        $form = $this->createForm(RackType::class, $rack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rack);
            $entityManager->flush();

            return $this->redirectToRoute('app_rack_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rack/new.html.twig', [
            'rack' => $rack,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rack_show', methods: ['GET'])]
    public function show(Rack $rack): Response
    {
        return $this->render('rack/show.html.twig', [
            'rack' => $rack,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rack_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rack $rack, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RackType::class, $rack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_rack_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rack/edit.html.twig', [
            'rack' => $rack,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rack_delete', methods: ['POST'])]
    public function delete(Request $request, Rack $rack, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rack->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($rack);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_rack_index', [], Response::HTTP_SEE_OTHER);
    }
}
