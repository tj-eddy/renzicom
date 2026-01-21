<?php

namespace App\Entity;

use App\Repository\DistributionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DistributionRepository::class)]
class Distribution
{
    // Constantes pour les statuts (en string maintenant)
    public const STATUS_PREPARING = 'en_preparation';
    public const STATUS_IN_PROGRESS = 'en_cours';
    public const STATUS_DELIVERED = 'livre';
    public const STATUS_CANCELLED = 'annule';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $status = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $destination = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;

    public function __construct()
    {
        $this->createAt = new \DateTimeImmutable();
        $this->status = self::STATUS_PREPARING; // Statut par défaut
    }

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Retourne le libellé du statut pour l'affichage
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PREPARING => 'distribution.status.preparing',
            self::STATUS_IN_PROGRESS => 'distribution.status.in_progress',
            self::STATUS_DELIVERED => 'distribution.status.delivered',
            self::STATUS_CANCELLED => 'distribution.status.cancelled',
            default => 'distribution.status.unknown',
        };
    }

    /**
     * Retourne la classe CSS du badge selon le statut
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PREPARING, self::STATUS_CANCELLED, self::STATUS_IN_PROGRESS, self::STATUS_DELIVERED => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    /**
     * Retourne tous les statuts disponibles
     */
    public static function getStatusChoices(): array
    {
        return [
            'distribution.status.preparing' => self::STATUS_PREPARING,
            'distribution.status.in_progress' => self::STATUS_IN_PROGRESS,
            'distribution.status.delivered' => self::STATUS_DELIVERED,
            'distribution.status.cancelled' => self::STATUS_CANCELLED,
        ];
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): static
    {
        $this->destination = $destination;
        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;
        return $this;
    }
}
