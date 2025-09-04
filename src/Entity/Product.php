<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use App\Value\Money;
use App\Value\Sku;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
#[ORM\UniqueConstraint(name: 'unique_products_sku', columns: ['sku'])]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 64)]
    private string $sku;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(name: 'unit_price_cents', type: 'integer')]
    private int $unitPriceCents;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Promotion::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $promotions;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: OrderItem::class)]
    private Collection $orderItems;

    public function __construct()
    {
        $this->promotions = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string|Sku $sku): self
    {
        $this->sku = $sku instanceof Sku ? $sku->toString() : $sku;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUnitPriceCents(): int
    {
        return $this->unitPriceCents;
    }

    public function getUnitPrice(): Money
    {
        return Money::ofCents($this->unitPriceCents);
    }

    public function setUnitPrice(Money $money): self
    {
        $this->unitPriceCents = $money->getCents();
        return $this;
    }

    public function setUnitPriceCents(int $unitPriceCents): self
    {
        $this->unitPriceCents = $unitPriceCents;

        return $this;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPromotions(): Collection
    {
        return $this->promotions;
    }
}
