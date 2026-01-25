<?php

namespace App\Entity;

use App\Repository\HotelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HotelRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Hotel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $contactPhone = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, Display>
     */
    #[ORM\OneToMany(targetEntity: Display::class, mappedBy: 'hotel', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $displays;

    public function __construct()
    {
        $this->displays = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    public function setContactName(?string $contactName): static
    {
        $this->contactName = $contactName;
        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?string $contactPhone): static
    {
        $this->contactPhone = $contactPhone;
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

    /**
     * @return Collection<int, Display>
     */
    public function getDisplays(): Collection
    {
        return $this->displays;
    }

    public function addDisplay(Display $display): static
    {
        if (!$this->displays->contains($display)) {
            $this->displays->add($display);
            $display->setHotel($this);
        }
        return $this;
    }

    public function removeDisplay(Display $display): static
    {
        if ($this->displays->removeElement($display)) {
            if ($display->getHotel() === $this) {
                $display->setHotel(null);
            }
        }
        return $this;
    }

    /**
     * Compter le nombre total de racks dans tous les displays
     */
    public function getTotalRacks(): int
    {
        $total = 0;
        foreach ($this->displays as $display) {
            $total += $display->getRacks()->count();
        }
        return $total;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
