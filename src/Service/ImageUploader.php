<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageUploader
{
    private string $warehouseImagesDirectory;
    private string $productImagesDirectory;
    private string $avatarImagesDirectory;
    private SluggerInterface $slugger;

    public function __construct(
        string $warehouseImagesDirectory,
        string $productImagesDirectory,
        string $avatarImagesDirectory,
        SluggerInterface $slugger
    ) {
        $this->warehouseImagesDirectory = $warehouseImagesDirectory;
        $this->productImagesDirectory = $productImagesDirectory;
        $this->avatarImagesDirectory = $avatarImagesDirectory;
        $this->slugger = $slugger;
    }

    /**
     * @throws Exception
     */
    public function upload(UploadedFile $file): string
    {
        return $this->uploadWarehouse($file);
    }

    /**
     * @throws Exception
     */
    public function uploadWarehouse(UploadedFile $file): string
    {
        return $this->uploadFile($file, $this->warehouseImagesDirectory);
    }

    /**
     * @throws Exception
     */
    public function uploadProduct(UploadedFile $file): string
    {
        return $this->uploadFile($file, $this->productImagesDirectory);
    }

    /**
     * @throws Exception
     */
    public function uploadAvatar(UploadedFile $file): string
    {
        return $this->uploadFile($file, $this->avatarImagesDirectory);
    }

    private function uploadFile(UploadedFile $file, string $targetDirectory): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($targetDirectory, $fileName);
        } catch (FileException $e) {
            throw new Exception('Erreur lors de l\'upload du fichier');
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

    public function removeAvatar(string $filename): void
    {
        $this->removeFile($filename, $this->avatarImagesDirectory);
    }


    private function removeFile(string $filename, string $targetDirectory): void
    {
        $filePath = $targetDirectory.'/'.$filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
