<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente un produit/magazine (Paris Match, Elle, Geo, etc.)
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'product')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $yearEdition = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $variant = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Stock>
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Stock::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $stocks;

    /**
     * @var Collection<int, Rack>
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Rack::class)]
    private Collection $racks;

    /**
     * @var Collection<int, Distribution>
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Distribution::class)]
    private Collection $distributions;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
        $this->racks = new ArrayCollection();
        $this->distributions = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->variant = [];
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

    public function getYearEdition(): ?int
    {
        return $this->yearEdition;
    }

    public function setYearEdition(?int $yearEdition): static
    {
        $this->yearEdition = $yearEdition;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getVariant(): ?array
    {
        return $this->variant;
    }

    public function setVariant(?array $variant): static
    {
        $this->variant = $variant;

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
            $stock->setProduct($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): static
    {
        if ($this->stocks->removeElement($stock)) {
            if ($stock->getProduct() === $this) {
                $stock->setProduct(null);
            }
        }

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
            $rack->setProduct($this);
        }

        return $this;
    }

    public function removeRack(Rack $rack): static
    {
        if ($this->racks->removeElement($rack)) {
            if ($rack->getProduct() === $this) {
                $rack->setProduct(null);
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
            $distribution->setProduct($this);
        }

        return $this;
    }

    public function removeDistribution(Distribution $distribution): static
    {
        if ($this->distributions->removeElement($distribution)) {
            if ($distribution->getProduct() === $this) {
                $distribution->setProduct(null);
            }
        }

        return $this;
    }
}
