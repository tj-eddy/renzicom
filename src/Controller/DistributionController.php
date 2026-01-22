<?php

namespace App\Controller;

use App\Entity\Distribution;
use App\Form\DistributionType;
use App\Repository\DistributionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/distribution')]
final class DistributionController extends AbstractController
{
    #[Route(name: 'app_distribution_index', methods: ['GET'])]
    public function index(DistributionRepository $distributionRepository): Response
    {
        return $this->render('distribution/index.html.twig', [
            'distributions' => $distributionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_distribution_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $distribution = new Distribution();
        $form = $this->createForm(DistributionType::class, $distribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($distribution);
            $entityManager->flush();

            return $this->redirectToRoute('app_distribution_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('distribution/new.html.twig', [
            'distribution' => $distribution,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_distribution_show', methods: ['GET'])]
    public function show(Distribution $distribution): Response
    {
        return $this->render('distribution/show.html.twig', [
            'distribution' => $distribution,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_distribution_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Distribution $distribution, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DistributionType::class, $distribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_distribution_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('distribution/edit.html.twig', [
            'distribution' => $distribution,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_distribution_delete', methods: ['POST'])]
    public function delete(Request $request, Distribution $distribution, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$distribution->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($distribution);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_distribution_index', [], Response::HTTP_SEE_OTHER);
    }
}
