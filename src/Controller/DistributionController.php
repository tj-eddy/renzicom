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
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/distribution')]
class DistributionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator
    ) {
    }

    #[Route('/', name: 'app_distribution_index', methods: ['GET'])]
    public function index(DistributionRepository $distributionRepository): Response
    {
        return $this->render('distribution/index.html.twig', [
            'distributions' => $distributionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_distribution_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $distribution = new Distribution();
        $form = $this->createForm(DistributionType::class, $distribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le statut est "delivered", mettre à jour completedAt
            if ($distribution->getStatus() === Distribution::STATUS_DELIVERED) {
                $distribution->setCompletedAt(new \DateTimeImmutable());
            }

            $this->entityManager->persist($distribution);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('messages.success.created'));

            return $this->redirectToRoute('app_distribution_index');
        }

        return $this->render('distribution/new.html.twig', [
            'distribution' => $distribution,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_distribution_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Distribution $distribution): Response
    {
        $oldStatus = $distribution->getStatus();

        $form = $this->createForm(DistributionType::class, $distribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le statut passe à "delivered", mettre à jour completedAt
            if ($distribution->getStatus() === Distribution::STATUS_DELIVERED &&
                $oldStatus !== Distribution::STATUS_DELIVERED) {
                $distribution->setCompletedAt(new \DateTimeImmutable());
            }

            // Si on revient en arrière depuis delivered, réinitialiser completedAt
            if ($distribution->getStatus() !== Distribution::STATUS_DELIVERED &&
                $oldStatus === Distribution::STATUS_DELIVERED) {
                $distribution->setCompletedAt(null);
            }

            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('messages.success.updated'));

            return $this->redirectToRoute('app_distribution_index');
        }

        return $this->render('distribution/edit.html.twig', [
            'distribution' => $distribution,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_distribution_delete', methods: ['POST'])]
    public function delete(Request $request, Distribution $distribution): Response
    {
        if ($this->isCsrfTokenValid('delete' . $distribution->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($distribution);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('messages.success.deleted'));
        }

        return $this->redirectToRoute('app_distribution_index');
    }
}
