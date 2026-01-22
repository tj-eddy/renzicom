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
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/display')]
class DisplayController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface    $translator
    )
    {
    }

    #[Route('/', name: 'app_display_index', methods: ['GET'])]
    public function index(DisplayRepository $displayRepository): Response
    {
        return $this->render('display/index.html.twig', [
            'displays' => $displayRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_display_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $display = new Display();
        $form = $this->createForm(DisplayType::class, $display);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($display);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('messages.success.created'));

            return $this->redirectToRoute('app_display_index');
        }

        return $this->render('display/new.html.twig', [
            'display' => $display,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_display_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Display $display): Response
    {
        $form = $this->createForm(DisplayType::class, $display);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('messages.success.updated'));

            return $this->redirectToRoute('app_display_index');
        }

        return $this->render('display/edit.html.twig', [
            'display' => $display,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_display_delete', methods: ['POST'])]
    public function delete(Request $request, Display $display): Response
    {
        if ($this->isCsrfTokenValid('delete' . $display->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($display);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('messages.success.deleted'));
        }

        return $this->redirectToRoute('app_display_index');
    }
}
