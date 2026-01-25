<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Form\InterventionType;
use App\Service\ImageUploader;
use App\Repository\RackRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\InterventionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/intervention')]
class InterventionController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {}
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
                    $this->addFlash('danger', $this->translator->trans('intervention.messages.photo_upload_error'));
                }
            }

            // Gérer l'upload de la photo après
            $photoAfterFile = $form->get('photoAfter')->getData();
            if ($photoAfterFile) {
                try {
                    $photoAfterFilename = $imageUploader->uploadInterventionImage($photoAfterFile);
                    $intervention->setPhotoAfter($photoAfterFilename);
                } catch (\Exception $e) {
                    $this->addFlash('danger', $this->translator->trans('intervention.messages.photo_upload_error'));
                }
            }

            $rack = $intervention->getRack();
            $distribution = $intervention->getDistribution();
            $quantityAdded = $intervention->getQuantityAdded();

            // 1. Mise à jour de la table Rack
            $newRackQuantity = $rack->getCurrentQuantity() + $quantityAdded;

            // Validation: Vérifier si le rack dépasse sa capacité
            if ($newRackQuantity > $rack->getRequiredQuantity()) {
                $this->addFlash('error', $this->translator->trans('rack.messages.exceeds_capacity', [
                    '%added%' => $quantityAdded,
                    '%available%' => $rack->getRequiredQuantity() - $rack->getCurrentQuantity(),
                ]));

                return $this->render('intervention/new.html.twig', [
                    'intervention' => $intervention,
                    'form' => $form,
                ]);
            }

            $rack->setCurrentQuantity($newRackQuantity);

            // Mise à jour du produit dans le rack
            if ($distribution->getProduct()) {
                $rack->setProduct($distribution->getProduct());
            }

            // 2. Déduction du stock de la distribution
            // On déduit la quantité ajoutée au rack du stock total de la distribution
            $distribution->setQuantity(max(0, $distribution->getQuantity() - $quantityAdded));

            $entityManager->persist($intervention);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('intervention.messages.created'));

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
        if (
            !$this->isGranted('ROLE_ADMIN')
            && $intervention->getDistribution()->getUser() !== $this->getUser()
        ) {
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
        ImageUploader $imageUploader,
    ): Response {
        // Vérifier si l'utilisateur a le droit de modifier cette intervention
        if (
            !$this->isGranted('ROLE_ADMIN')
            && $intervention->getDistribution()->getUser() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException();
        }

        // Sauvegarder l'ancienne quantité pour ajuster le rack et la distribution
        $oldQuantity = $intervention->getQuantityAdded();
        $oldRack = $intervention->getRack();
        $oldDistribution = $intervention->getDistribution();

        $form = $this->createForm(InterventionType::class, $intervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de la photo avant
            $photoBeforeFile = $form->get('photoBefore')->getData();
            if ($photoBeforeFile) {
                if ($intervention->getPhotoBefore()) {
                    $imageUploader->removeInterventionImage($intervention->getPhotoBefore());
                }
                try {
                    $photoBeforeFilename = $imageUploader->uploadInterventionImage($photoBeforeFile);
                    $intervention->setPhotoBefore($photoBeforeFilename);
                } catch (\Exception $e) {
                    $this->addFlash('danger', $this->translator->trans('intervention.messages.photo_upload_error'));
                }
            }

            // Gérer l'upload de la photo après
            $photoAfterFile = $form->get('photoAfter')->getData();
            if ($photoAfterFile) {
                if ($intervention->getPhotoAfter()) {
                    $imageUploader->removeInterventionImage($intervention->getPhotoAfter());
                }
                try {
                    $photoAfterFilename = $imageUploader->uploadInterventionImage($photoAfterFile);
                    $intervention->setPhotoAfter($photoAfterFilename);
                } catch (\Exception $e) {
                    $this->addFlash('danger', $this->translator->trans('intervention.messages.photo_upload_error'));
                }
            }

            $rack = $intervention->getRack();
            $distribution = $intervention->getDistribution();
            $newQuantity = $intervention->getQuantityAdded();

            // Ajustement si le rack ou la distribution a changé (très rare en édition mais possible)
            if ($oldRack !== $rack) {
                // Restaurer l'ancien rack
                $oldRack->setCurrentQuantity(max(0, $oldRack->getCurrentQuantity() - $oldQuantity));
                // Mettre à jour le nouveau rack
                $rack->setCurrentQuantity($rack->getCurrentQuantity() + $newQuantity);
            } else {
                // Ajuster la quantité sur le même rack
                $diff = $newQuantity - $oldQuantity;
                $rack->setCurrentQuantity($rack->getCurrentQuantity() + $diff);
            }

            // Validation: Vérifier si le rack dépasse sa capacité
            if ($rack->getCurrentQuantity() > $rack->getRequiredQuantity()) {
                $this->addFlash('error', $this->translator->trans('rack.messages.exceeds_capacity', [
                    '%added%' => $newQuantity,
                    '%available%' => $rack->getRequiredQuantity() - ($rack->getCurrentQuantity() - $newQuantity),
                ]));

                // On ne flush pas pour annuler les changements en mémoire si possible, 
                // mais ici on redirige ou on réaffiche le formulaire.
                // Note: En Symfony/Doctrine, les changements sont en mémoire jusqu'au flush.
                return $this->render('intervention/edit.html.twig', [
                    'intervention' => $intervention,
                    'form' => $form,
                ]);
            }

            // Ajustement de la distribution
            if ($oldDistribution !== $distribution) {
                // Restaurer l'ancienne distribution
                $oldDistribution->setQuantity($oldDistribution->getQuantity() + $oldQuantity);
                // Déduire de la nouvelle distribution
                $distribution->setQuantity(max(0, $distribution->getQuantity() - $newQuantity));
            } else {
                // Ajuster la quantité sur la même distribution
                $diff = $newQuantity - $oldQuantity;
                $distribution->setQuantity(max(0, $distribution->getQuantity() - $diff));
            }

            // Mise à jour du produit dans le rack (au cas où la distribution a changé de produit)
            if ($distribution->getProduct()) {
                $rack->setProduct($distribution->getProduct());
            }

            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('intervention.messages.updated'));

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
        ImageUploader $imageUploader,
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $intervention->getId(), $request->getPayload()->getString('_token'))) {
            $rack = $intervention->getRack();
            $distribution = $intervention->getDistribution();
            $quantityAdded = $intervention->getQuantityAdded();

            // 1. Restaurer la quantité du rack
            $rack->setCurrentQuantity(max(0, $rack->getCurrentQuantity() - $quantityAdded));

            // 2. Restaurer le stock de la distribution
            $distribution->setQuantity($distribution->getQuantity() + $quantityAdded);

            // Supprimer les photos si elles existent
            if ($intervention->getPhotoBefore()) {
                $imageUploader->removeInterventionImage($intervention->getPhotoBefore());
            }
            if ($intervention->getPhotoAfter()) {
                $imageUploader->removeInterventionImage($intervention->getPhotoAfter());
            }

            $entityManager->remove($intervention);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('intervention.messages.deleted'));
        } else {
            $this->addFlash('error', $this->translator->trans('exception.invalid_token'));
        }

        return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
    }
}
