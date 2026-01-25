<?php
namespace App\Entity;

use App\Repository\RackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RackRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Rack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $position = 0;

    #[ORM\Column]
    private ?int $requiredQuantity = 0;

    #[ORM\Column]
    private ?int $currentQuantity = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'racks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Display $display = null;

    #[ORM\ManyToOne(inversedBy: 'racks')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Product $product = null;

    /**
     * @var Collection<int, Intervention>
     */
    #[ORM\OneToMany(targetEntity: Intervention::class, mappedBy: 'rack', orphanRemoval: true)]
    private Collection $interventions;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getRequiredQuantity(): ?int
    {
        return $this->requiredQuantity;
    }

    public function setRequiredQuantity(int $requiredQuantity): static
    {
        $this->requiredQuantity = $requiredQuantity;
        return $this;
    }

    public function getCurrentQuantity(): ?int
    {
        return $this->currentQuantity;
    }

    public function setCurrentQuantity(int $currentQuantity): static
    {
        $this->currentQuantity = $currentQuantity;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
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
     * Vérifier si le rack a besoin d'un remplissage
     */
    public function needsRefill(): bool
    {
        return $this->currentQuantity < $this->requiredQuantity;
    }

    /**
     * Calculer le pourcentage de remplissage
     */
    public function getFillPercentage(): int
    {
        if ($this->requiredQuantity === 0) {
            return 0;
        }
        return (int) (($this->currentQuantity / $this->requiredQuantity) * 100);
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
