<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Stock;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Security\PermissionChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/product')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {}

    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/export', name: 'app_product_export', methods: ['GET'])]
    public function export(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        $response = new StreamedResponse(function () use ($products) {
            $handle = fopen('php://output', 'w+');
            // Byte Order Mark for UTF-8 compatibility with Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['ID', 'Nom', 'Entrepot', 'Stock', 'Date Creation'], ';');

            foreach ($products as $product) {
                $stocks = $product->getStocks();
                if ($stocks->isEmpty()) {
                    fputcsv($handle, [
                        $product->getId(),
                        $product->getName(),
                        'N/A',
                        0,
                        $product->getCreatedAt() ? $product->getCreatedAt()->format('Y-m-d H:i:s') : ''
                    ], ';');
                } else {
                    foreach ($stocks as $stock) {
                        fputcsv($handle, [
                            $product->getId(),
                            $product->getName(),
                            $stock->getWarehouse() ? $stock->getWarehouse()->getName() : 'N/A',
                            $stock->getQuantity(),
                            $product->getCreatedAt() ? $product->getCreatedAt()->format('Y-m-d H:i:s') : ''
                        ], ';');
                    }
                }
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="produits_export_' . date('Y-m-d_H-i-s') . '.csv"');

        return $response;
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        PermissionChecker $permissionChecker
    ): Response {
        // Vérification de permission: seuls les admins peuvent créer des produits
        if (!$permissionChecker->canCreateProductOrWarehouse()) {
            $this->addFlash('error', $this->translator->trans('access.denied.create_product'));
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('product_images_directory'),
                        $newFilename
                    );
                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', $this->translator->trans('messages.error.image_upload'));
                }
            }

            // Traiter les stocks
            foreach ($product->getStocks() as $stock) {
                $stock->setProduct($product);
                $entityManager->persist($stock);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('product.created'));

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
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
    public function edit(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        PermissionChecker $permissionChecker
    ): Response {
        // Vérification de permission: seuls les admins peuvent modifier des produits
        if (!$permissionChecker->canCreateProductOrWarehouse()) {
            $this->addFlash('error', $this->translator->trans('access.denied.create_product'));
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                // Supprimer l'ancienne image si elle existe
                if ($product->getImage()) {
                    $oldImagePath = $this->getParameter('product_images_directory') . '/' . $product->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('product_images_directory'),
                        $newFilename
                    );
                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', $this->translator->trans('messages.error.image_upload'));
                }
            }

            // Traiter les nouveaux stocks
            foreach ($product->getStocks() as $stock) {
                if (!$stock->getId()) {
                    $stock->setProduct($product);
                }
                $entityManager->persist($stock);
            }

            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('product.updated'));

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager,
        PermissionChecker $permissionChecker
    ): Response {
        // Vérification de permission: seuls les admins peuvent supprimer
        if (!$permissionChecker->canDelete()) {
            $this->addFlash('error', $this->translator->trans('access.denied.delete_any'));
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            // Supprimer l'image si elle existe
            if ($product->getImage()) {
                $imagePath = $this->getParameter('product_images_directory') . '/' . $product->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('product.deleted'));
        } else {
            $this->addFlash('error', $this->translator->trans('exception.invalid_token'));
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
