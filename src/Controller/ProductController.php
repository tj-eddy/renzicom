<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/product')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface    $translator,
        private readonly ImageUploader          $imageUploader
    )
    {
    }

    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                try {
                    $imageName = $this->imageUploader->uploadProductImage($imageFile);
                    $product->setImage($imageName);
                } catch (Exception $e) {
                    $this->addFlash('error', $this->translator->trans('messages.error.image_upload'));
                }
            }

            // Gestion du champ variant (JSON)
            $variantData = $form->get('variant')->getData();
            if ($variantData) {
                try {
                    $variantArray = json_decode($variantData, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $product->setVariant($variantArray);
                    } else {
                        $product->setVariant([]);
                    }
                } catch (\Exception $e) {
                    $product->setVariant([]);
                }
            }

            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('messages.success.created'));

            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);

        // Pré-remplir le champ variant avec le JSON existant
        if ($product->getVariant()) {
            $form->get('variant')->setData(json_encode($product->getVariant(), JSON_PRETTY_PRINT));
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                // Supprimer l'ancienne image si elle existe
                if ($product->getImage()) {
                    try {
                        $this->imageUploader->removeProductImage($product->getImage());
                    } catch (\Exception $e) {
                        // Log l'erreur mais continue
                    }
                }

                try {
                    $imageName = $this->imageUploader->uploadProductImage($imageFile);
                    $product->setImage($imageName);
                } catch (Exception $e) {
                    $this->addFlash('error', $this->translator->trans('messages.error.image_upload'));
                }
            }

            $variantData = $form->get('variant')->getData();
            if ($variantData) {
                try {
                    $variantArray = json_decode($variantData, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $product->setVariant($variantArray);
                    }
                } catch (\Exception $e) {
                }
            } else {
                $product->setVariant(null);
            }

            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('messages.success.updated'));

            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            if ($product->getImage()) {
                try {
                    $this->imageUploader->removeProductImage($product->getImage());
                } catch (\Exception $e) {
                }
            }

            $this->entityManager->remove($product);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('messages.success.deleted'));
        }

        return $this->redirectToRoute('app_product_index');
    }
}
