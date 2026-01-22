<?php

namespace App\Entity;

use App\Repository\RackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente un espace/rack dans un présentoir (ex: Rack 1, Rack A)
 */
#[ORM\Entity(repositoryClass: RackRepository::class)]
#[ORM\Table(name: 'rack')]
class Rack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Display::class, inversedBy: 'racks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Display $display = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'racks')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Product $product = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $requiredQuantity = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $currentQuantity = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Intervention>
     */
    #[ORM\OneToMany(mappedBy: 'rack', targetEntity: Intervention::class)]
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

    public function getDisplay(): ?Display
    {
        return $this->display;
    }

    public function setDisplay(?Display $display): static
    {
        $this->display = $display;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getRequiredQuantity(): int
    {
        return $this->requiredQuantity;
    }

    public function setRequiredQuantity(int $requiredQuantity): static
    {
        $this->requiredQuantity = $requiredQuantity;

        return $this;
    }

    public function getCurrentQuantity(): int
    {
        return $this->currentQuantity;
    }

    public function setCurrentQuantity(int $currentQuantity): static
    {
        $this->currentQuantity = $currentQuantity;

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
            $intervention->setRack($this);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            if ($intervention->getRack() === $this) {
                $intervention->setRack(null);
            }
        }

        return $this;
    }

    /**
     * Calcule le taux de remplissage du rack en pourcentage
     */
    public function getFillRate(): float
    {
        if ($this->requiredQuantity === 0) {
            return 0.0;
        }

        return ($this->currentQuantity / $this->requiredQuantity) * 100;
    }

    /**
     * Vérifie si le rack nécessite un réapprovisionnement
     */
    public function needsRefill(int $threshold = 50): bool
    {
        return $this->getFillRate() < $threshold;
    }
}
