<?php

namespace App\Entity;

use App\Repository\RackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RackRepository::class)]
class Rack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\ManyToOne(inversedBy: 'racks')]
    private ?Warehouse $warehouse = null;

    /**
     * @var Collection<int, Stock>
     */
    #[ORM\OneToMany(targetEntity: Stock::class, mappedBy: 'rack')]
    private Collection $stocks;

    /**
     * @var Collection<int, Distribution>
     */
    #[ORM\OneToMany(targetEntity: Distribution::class, mappedBy: 'rack')]
    private Collection $distributions;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
        $this->distributions = new ArrayCollection();
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

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

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): static
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): static
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->setRack($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): static
    {
        if ($this->stocks->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getRack() === $this) {
                $stock->setRack(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Distribution>
     */
    public function getDistributions(): Collection
    {
        return $this->distributions;
    }

    public function addDistribution(Distribution $distribution): static
    {
        if (!$this->distributions->contains($distribution)) {
            $this->distributions->add($distribution);
            $distribution->setRack($this);
        }

        return $this;
    }

    public function removeDistribution(Distribution $distribution): static
    {
        if ($this->distributions->removeElement($distribution)) {
            // set the owning side to null (unless already changed)
            if ($distribution->getRack() === $this) {
                $distribution->setRack(null);
            }
        }

        return $this;
    }
}
