<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/user')]
final class UserController extends AbstractController
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', ['users' => $userRepository->findBy([], ['id' => 'DESC'])]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ImageUploader $imageUploader): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('access.denied.create'));

            return $this->redirectToRoute('app_user_index');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // PWD
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // ROLE
            $selectedRole = $form->get('role')->getData();
            if ($selectedRole) {
                $user->setRole($selectedRole);
            }
            // AVATAR
            $avatarFile = $form->get('avatarFile')->getData();
            if ($avatarFile) {
                try {
                    $avatarFileName = $imageUploader->uploadAvatar($avatarFile);
                    $user->setAvatar($avatarFileName);
                } catch (\Exception $e) {
                    $this->addFlash('error', $this->translator->trans('user.avatar.upload_error'));
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('user.created'));

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', ['user' => $user, 'form' => $form]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ImageUploader $imageUploader
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('access.denied.edit'));

            return $this->redirectToRoute('app_user_index');
        }

        $form = $this->createForm(UserType::class, $user);

        // Pré-remplir le formulaire avec le rôle actuel
        $form->get('role')->setData($user->getRole());

        $form->handleRequest($request);

        $oldAvatar = $user->getAvatar();

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le mot de passe (seulement si rempli)
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Gérer le rôle
            $selectedRole = $form->get('role')->getData();
            if ($selectedRole) {
                $user->setRole($selectedRole);
            }

            $avatarFile = $form->get('avatarFile')->getData();
            if ($avatarFile) {
                try {
                    // Supprimer l'ancien avatar si existe
                    if ($oldAvatar) {
                        $imageUploader->removeAvatar($oldAvatar);
                    }

                    $avatarFileName = $imageUploader->uploadAvatar($avatarFile);
                    $user->setAvatar($avatarFileName);
                } catch (\Exception $e) {
                    $this->addFlash('error', $this->translator->trans('user.avatar.upload_error'));
                }
            }

            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('user.updated'));

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', ['user' => $user, 'form' => $form]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        ImageUploader $imageUploader
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            if ($user->getAvatar()) {
                $imageUploader->removeAvatar($user->getAvatar());
            }
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', $this->translator->trans('user.deleted'));
        } else {
            $this->addFlash('error', $this->translator->trans('exception.invalid_token'));
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
