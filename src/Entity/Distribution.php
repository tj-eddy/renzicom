<?php

namespace App\Entity;

use App\Repository\DistributionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente une tournée de livraison effectuée par un livreur.
 */
#[ORM\Entity(repositoryClass: DistributionRepository::class)]
#[ORM\Table(name: 'distribution')]
class Distribution
{
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'distributions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'distributions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Product $product = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $quantity = 0;

    #[ORM\Column(type: 'string', length: 50, options: ['default' => self::STATUS_PREPARING])]
    private string $status = self::STATUS_PREPARING;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $destination = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    /**
     * @var Collection<int, Intervention>
     */
    #[ORM\OneToMany(mappedBy: 'distribution', targetEntity: Intervention::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $interventions;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(?string $destination): static
    {
        $this->destination = $destination;

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

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    /**
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setDistribution($this);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            if ($intervention->getDistribution() === $this) {
                $intervention->setDistribution(null);
            }
        }

        return $this;
    }

    /**
     * Vérifie si la distribution est terminée.
     */
    public function isCompleted(): bool
    {
        return self::STATUS_DELIVERED === $this->status;
    }

    /**
     * Vérifie si la distribution est en cours.
     */
    public function isInProgress(): bool
    {
        return self::STATUS_IN_PROGRESS === $this->status;
    }

    /**
     * Marque la distribution comme terminée.
     */
    public function markAsCompleted(): static
    {
        $this->status = self::STATUS_DELIVERED;
        $this->completedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * Calcule la quantité restante non distribuée.
     */
    public function getRemainingQuantity(): int
    {
        $distributed = 0;
        foreach ($this->interventions as $intervention) {
            $distributed += $intervention->getQuantityAdded();
        }

        return $this->quantity - $distributed;
    }

    /**
     * Calcule la quantité totale distribuée via les interventions.
     */
    public function getQuantityDistributed(): int
    {
        $total = 0;
        foreach ($this->interventions as $intervention) {
            $total += $intervention->getQuantityAdded();
        }

        return $total;
    }

    /**
     * Calcule la quantité restante dans la voiture du livreur.
     */
    public function getQuantityRemaining(): int
    {
        return max(0, $this->quantity - $this->getQuantityDistributed());
    }

    /**
     * Vérifie si la distribution est complète.
     */
    public function isFullyDistributed(): bool
    {
        return 0 === $this->getQuantityRemaining();
    }

    /**
     * Calcule le pourcentage de distribution.
     */
    public function getDistributionPercentage(): float
    {
        if (0 === $this->quantity) {
            return 0;
        }

        return ($this->getQuantityDistributed() / $this->quantity) * 100;
    }

    public function __toString(): string
    {
        return sprintf('#%d - %s', $this->id ?? 0, $this->product?->getName() ?? 'N/A');
    }
}
