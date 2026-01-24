<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente une intervention de remplissage d'un rack lors d'une distribution.
 */
#[ORM\Entity(repositoryClass: InterventionRepository::class)]
#[ORM\Table(name: 'intervention')]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Distribution::class, inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Distribution $distribution = null;

    #[ORM\ManyToOne(targetEntity: Rack::class, inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Rack $rack = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $quantityAdded = 0;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $photoBefore = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $photoAfter = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDistribution(): ?Distribution
    {
        return $this->distribution;
    }

    public function setDistribution(?Distribution $distribution): static
    {
        $this->distribution = $distribution;

        return $this;
    }

    public function getRack(): ?Rack
    {
        return $this->rack;
    }

    public function setRack(?Rack $rack): static
    {
        $this->rack = $rack;

        return $this;
    }

    public function getQuantityAdded(): int
    {
        return $this->quantityAdded;
    }

    public function setQuantityAdded(int $quantityAdded): static
    {
        $this->quantityAdded = $quantityAdded;

        return $this;
    }

    public function getPhotoBefore(): ?string
    {
        return $this->photoBefore;
    }

    public function setPhotoBefore(?string $photoBefore): static
    {
        $this->photoBefore = $photoBefore;

        return $this;
    }

    public function getPhotoAfter(): ?string
    {
        return $this->photoAfter;
    }

    public function setPhotoAfter(?string $photoAfter): static
    {
        $this->photoAfter = $photoAfter;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Vérifie si l'intervention possède des photos.
     */
    public function hasPhotos(): bool
    {
        return null !== $this->photoBefore || null !== $this->photoAfter;
    }
}
