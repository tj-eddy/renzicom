<?php
// src/Entity/Display.php

namespace App\Entity;

use App\Repository\DisplayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DisplayRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Display
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'displays')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Hotel $hotel = null;

    /**
     * @var Collection<int, Rack>
     */
    #[ORM\OneToMany(targetEntity: Rack::class, mappedBy: 'display', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $racks;

    public function __construct()
    {
        $this->racks = new ArrayCollection();
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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
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

    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    public function setHotel(?Hotel $hotel): static
    {
        $this->hotel = $hotel;
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

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
