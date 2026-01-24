<?php

namespace App\Entity;

use App\Repository\DisplayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente un présentoir dans un hôtel (ex: Présentoir Lobby, Présentoir Étage 2).
 */
#[ORM\Entity(repositoryClass: DisplayRepository::class)]
#[ORM\Table(name: 'display')]
class Display
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Hotel::class, inversedBy: 'displays')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Hotel $hotel = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Rack>
     */
    #[ORM\OneToMany(mappedBy: 'display', targetEntity: Rack::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $racks;

    public function __construct()
    {
        $this->racks = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    public function setHotel(?Hotel $hotel): static
    {
        $this->hotel = $hotel;

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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

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
     * @return Collection<int, Rack>
     */
    public function getRacks(): Collection
    {
        return $this->racks;
    }

    public function addRack(Rack $rack): static
    {
        if (!$this->racks->contains($rack)) {
            $this->racks->add($rack);
            $rack->setDisplay($this);
        }

        return $this;
    }

    public function removeRack(Rack $rack): static
    {
        if ($this->racks->removeElement($rack)) {
            if ($rack->getDisplay() === $this) {
                $rack->setDisplay(null);
            }
        }

        return $this;
    }
}
