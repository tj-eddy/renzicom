<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageUploader
{
    private string $avatarImagesDirectory;
    private string $productImagesDirectory;
    private string $interventionImagesDirectory;
    private SluggerInterface $slugger;

    public function __construct(
        string $avatarImagesDirectory,
        string $productImagesDirectory,
        string $interventionImagesDirectory,
        SluggerInterface $slugger,
    ) {
        $this->avatarImagesDirectory = $avatarImagesDirectory;
        $this->productImagesDirectory = $productImagesDirectory;
        $this->interventionImagesDirectory = $interventionImagesDirectory;
        $this->slugger = $slugger;
    }

    /**
     * @throws \Exception
     */
    public function uploadAvatar(UploadedFile $file): string
    {
        return $this->uploadFile($file, $this->avatarImagesDirectory);
    }

    /**
     * @throws \Exception
     */
    public function uploadProductImage(UploadedFile $file): string
    {
        return $this->uploadFile($file, $this->productImagesDirectory);
    }

    /**
     * @throws \Exception
     */
    public function uploadInterventionImage(UploadedFile $file): string
    {
        return $this->uploadFile($file, $this->interventionImagesDirectory);
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

    public function removeAvatar(string $filename): void
    {
        $this->removeFile($filename, $this->avatarImagesDirectory);
    }

    public function removeProductImage(string $filename): void
    {
        $this->removeFile($filename, $this->productImagesDirectory);
    }

    public function removeInterventionImage(string $filename): void
    {
        $this->removeFile($filename, $this->interventionImagesDirectory);
    }

    private function removeFile(string $filename, string $targetDirectory): void
    {
        $filePath = $targetDirectory.'/'.$filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
