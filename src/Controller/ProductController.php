<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ProductType;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/product')]
class ProductController extends AbstractController
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('images')->getData();

            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $filename = $imageUploader->uploadProduct($imageFile);

                    $productImage = new ProductImage();
                    $productImage->setFilename($filename);
                    $product->addImage($productImage);
                }
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('product.created_successfully'));

            return $this->redirectToRoute('app_product_index', ['_locale' => $request->getLocale()]);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('images')->getData();

            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $filename = $imageUploader->uploadProduct($imageFile);

                    $productImage = new ProductImage();
                    $productImage->setFilename($filename);
                    $product->addImage($productImage);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('product.updated_successfully'));

            return $this->redirectToRoute('app_product_index', ['_locale' => $request->getLocale()]);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            foreach ($product->getImages() as $image) {
                $imageUploader->removeProduct($image->getFilename());
            }

            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('product.deleted_successfully'));
        }

        return $this->redirectToRoute('app_product_index', ['_locale' => $request->getLocale()]);
    }

    #[Route('/image/{id}/delete', name: 'app_product_image_delete', methods: ['POST'])]
    public function deleteImage(Request $request, ProductImage $image, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        $productId = $image->getProduct()->getId();

        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $imageUploader->removeProduct($image->getFilename());
            $entityManager->remove($image);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('product.image_deleted_successfully'));
        }

        return $this->redirectToRoute('app_product_edit', [
            'id' => $productId,
            '_locale' => $request->getLocale(),
        ]);
    }
}
