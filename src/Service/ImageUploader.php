<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageUploader
{
    private string $warehouseImagesDirectory;
    private string $productImagesDirectory;
    private SluggerInterface $slugger;

    public function __construct(
        string $warehouseImagesDirectory,
        string $productImagesDirectory,
        SluggerInterface $slugger
    ) {
        $this->warehouseImagesDirectory = $warehouseImagesDirectory;
        $this->productImagesDirectory = $productImagesDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file): string
    {
        return $this->uploadWarehouse($file);
    }

    public function uploadWarehouse(UploadedFile $file): string
    {
        return $this->uploadFile($file, $this->warehouseImagesDirectory);
    }

    public function uploadProduct(UploadedFile $file): string
    {
        return $this->uploadFile($file, $this->productImagesDirectory);
    }

    private function uploadFile(UploadedFile $file, string $targetDirectory): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($targetDirectory, $fileName);
        } catch (FileException $e) {
            throw new \Exception('Erreur lors de l\'upload du fichier');
        }

        return $fileName;
    }

    public function remove(string $filename): void
    {
        $this->removeWarehouse($filename);
    }

    public function removeWarehouse(string $filename): void
    {
        $this->removeFile($filename, $this->warehouseImagesDirectory);
    }

    public function removeProduct(string $filename): void
    {
        $this->removeFile($filename, $this->productImagesDirectory);
    }

    private function removeFile(string $filename, string $targetDirectory): void
    {
        $filePath = $targetDirectory.'/'.$filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
