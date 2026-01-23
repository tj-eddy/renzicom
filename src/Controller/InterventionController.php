<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Form\InterventionType;
use App\Repository\InterventionRepository;
use App\Repository\RackRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/intervention')]
class InterventionController extends AbstractController
{
    #[Route('/', name: 'app_intervention_index', methods: ['GET'])]
    public function index(InterventionRepository $interventionRepository): Response
    {
        // Pour admin : voir toutes les interventions
        // Pour livreur : voir seulement ses interventions
        if ($this->isGranted('ROLE_ADMIN')) {
            $interventions = $interventionRepository->findBy([], ['createdAt' => 'DESC']);
        } else {
            $interventions = $interventionRepository->createQueryBuilder('i')
                ->join('i.distribution', 'd')
                ->where('d.user = :user')
                ->setParameter('user', $this->getUser())
                ->orderBy('i.createdAt', 'DESC')
                ->getQuery()
                ->getResult();
        }

        return $this->render('intervention/index.html.twig', [
            'interventions' => $interventions,
        ]);
    }

    #[Route('/new', name: 'app_intervention_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ImageUploader $imageUploader,
        RackRepository $rackRepository,
    ): Response {
        $intervention = new Intervention();
        $form = $this->createForm(InterventionType::class, $intervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de la photo avant
            $photoBeforeFile = $form->get('photoBefore')->getData();
            if ($photoBeforeFile) {
                try {
                    $photoBeforeFilename = $imageUploader->uploadInterventionImage($photoBeforeFile);
                    $intervention->setPhotoBefore($photoBeforeFilename);
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de la photo avant');
                }
            }

            // Gérer l'upload de la photo après
            $photoAfterFile = $form->get('photoAfter')->getData();
            if ($photoAfterFile) {
                try {
                    $photoAfterFilename = $imageUploader->uploadInterventionImage($photoAfterFile);
                    $intervention->setPhotoAfter($photoAfterFilename);
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de la photo après');
                }
            }

            $result = $rackRepository->updateCurrentQuantityFromIntervention($intervention);

            if (!$result['success']) {
                $this->addFlash('error', $result['message']);
                return $this->redirectToRoute('app_intervention_new');
            }


            $entityManager->persist($intervention);
            $entityManager->flush();

            $this->addFlash('success', $result['message']);

            return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intervention/new.html.twig', [
            'intervention' => $intervention,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_intervention_show', methods: ['GET'])]
    public function show(Intervention $intervention): Response
    {
        // Vérifier si l'utilisateur a le droit de voir cette intervention
        if (!$this->isGranted('ROLE_ADMIN') &&
            $intervention->getDistribution()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('intervention/show.html.twig', [
            'intervention' => $intervention,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_intervention_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Intervention $intervention,
        EntityManagerInterface $entityManager,
        RackRepository $rackRepository,
        ImageUploader $imageUploader
    ): Response {
        // Vérifier si l'utilisateur a le droit de modifier cette intervention
        if (!$this->isGranted('ROLE_ADMIN') &&
            $intervention->getDistribution()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(InterventionType::class, $intervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de la photo avant
            $photoBeforeFile = $form->get('photoBefore')->getData();
            if ($photoBeforeFile) {
                // Supprimer l'ancienne photo si elle existe
                if ($intervention->getPhotoBefore()) {
                    $imageUploader->removeInterventionImage($intervention->getPhotoBefore());
                }

                try {
                    $photoBeforeFilename = $imageUploader->uploadInterventionImage($photoBeforeFile);
                    $intervention->setPhotoBefore($photoBeforeFilename);
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de la photo avant');
                }
            }

            // Gérer l'upload de la photo après
            $photoAfterFile = $form->get('photoAfter')->getData();
            if ($photoAfterFile) {
                // Supprimer l'ancienne photo si elle existe
                if ($intervention->getPhotoAfter()) {
                    $imageUploader->removeInterventionImage($intervention->getPhotoAfter());
                }

                try {
                    $photoAfterFilename = $imageUploader->uploadInterventionImage($photoAfterFile);
                    $intervention->setPhotoAfter($photoAfterFilename);
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de la photo après');
                }
            }

            $result = $rackRepository->updateCurrentQuantityFromIntervention($intervention);

            if (!$result['success']) {
                $this->addFlash('error', $result['message']);
                return $this->redirectToRoute('app_intervention_edit', ['id' => $intervention->getId()]);
            }

            $entityManager->flush();

            $this->addFlash('success', 'intervention.messages.updated');

            return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intervention/edit.html.twig', [
            'intervention' => $intervention,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_intervention_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Intervention $intervention,
        EntityManagerInterface $entityManager,
        ImageUploader $imageUploader
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$intervention->getId(), $request->getPayload()->getString('_token'))) {
            // Supprimer les photos si elles existent
            if ($intervention->getPhotoBefore()) {
                $imageUploader->removeInterventionImage($intervention->getPhotoBefore());
            }
            if ($intervention->getPhotoAfter()) {
                $imageUploader->removeInterventionImage($intervention->getPhotoAfter());
            }

            $entityManager->remove($intervention);
            $entityManager->flush();

            $this->addFlash('success', 'intervention.messages.deleted');
        }

        return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
    }
}
