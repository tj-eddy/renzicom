<?php

namespace App\Controller;

use App\Entity\Display;
use App\Form\DisplayType;
use App\Repository\DisplayRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/display')]
final class DisplayController extends AbstractController
{
    #[Route(name: 'app_display_index', methods: ['GET'])]
    public function index(DisplayRepository $displayRepository): Response
    {
        return $this->render('display/index.html.twig', [
            'displays' => $displayRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_display_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $display = new Display();
        $form = $this->createForm(DisplayType::class, $display);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($display);
            $entityManager->flush();

            return $this->redirectToRoute('app_display_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('display/new.html.twig', [
            'display' => $display,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_display_show', methods: ['GET'])]
    public function show(Display $display): Response
    {
        return $this->render('display/show.html.twig', [
            'display' => $display,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_display_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Display $display, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DisplayType::class, $display);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_display_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('display/edit.html.twig', [
            'display' => $display,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_display_delete', methods: ['POST'])]
    public function delete(Request $request, Display $display, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$display->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($display);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_display_index', [], Response::HTTP_SEE_OTHER);
    }
}
