<?php

namespace App\Controller;

use App\Entity\Rack;
use App\Form\RackType;
use App\Repository\RackRepository;
use App\Security\PermissionChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/rack')]
class RackController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private PermissionChecker $permissionChecker,
    ) {
    }

    #[Route('/', name: 'app_rack_index', methods: ['GET'])]
    public function index(RackRepository $rackRepository): Response
    {
        return $this->render('rack/index.html.twig', [
            'racks' => $rackRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_rack_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // Vérification de permission: seuls les admins peuvent créer des racks
        if (!$this->permissionChecker->canCreateProductOrWarehouse()) {
            $this->addFlash('error', $this->translator->trans('access.denied.create_rack'));
            return $this->redirectToRoute('app_rack_index');
        }

        $rack = new Rack();
        $form = $this->createForm(RackType::class, $rack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($rack);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('rack.created'));

            return $this->redirectToRoute('app_rack_index');
        }

        return $this->render('rack/new.html.twig', [
            'rack' => $rack,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rack_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rack $rack): Response
    {
        // Vérification de permission: seuls les admins peuvent modifier des racks
        if (!$this->permissionChecker->canCreateProductOrWarehouse()) {
            $this->addFlash('error', $this->translator->trans('access.denied.create_rack'));
            return $this->redirectToRoute('app_rack_index');
        }

        $form = $this->createForm(RackType::class, $rack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('rack.updated'));

            return $this->redirectToRoute('app_rack_index');
        }

        return $this->render('rack/edit.html.twig', [
            'rack' => $rack,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rack_delete', methods: ['POST'])]
    public function delete(Request $request, Rack $rack): Response
    {
        // Vérification de permission: seuls les admins peuvent supprimer
        if (!$this->permissionChecker->canDelete()) {
            $this->addFlash('error', $this->translator->trans('access.denied.delete_any'));
            return $this->redirectToRoute('app_rack_index');
        }

        if ($this->isCsrfTokenValid('delete' . $rack->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($rack);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('rack.deleted'));
        } else {
            $this->addFlash('error', $this->translator->trans('exception.invalid_token'));
        }

        return $this->redirectToRoute('app_rack_index');
    }
}
