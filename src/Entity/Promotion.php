<?php

namespace App\Entity;

use App\Enum\PromotionType;
use App\Repository\PromotionRepository;
use App\Value\Quantity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromotionRepository::class)]
#[ORM\Table(name: 'promotions')]
#[ORM\UniqueConstraint(name: 'promotions_product_type_qty', columns: ['product_id', 'type', 'n_qty'])]
class Promotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'promotions')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Product $product = null;

    #[ORM\Column(type: 'string', length: 32, enumType: PromotionType::class)]
    private PromotionType $type;

    #[ORM\Column(name: 'n_qty', type: 'integer')]
    private int $nQty;

    #[ORM\Column(name: 'special_price_cents', type: 'integer')]
    private int $specialPriceCents;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getType(): PromotionType
    {
        return $this->type;
    }

    public function setType(PromotionType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNQty(): int
    {
        return $this->nQty;
    }

    public function setNQty(int $nQty): self
    {
        $this->nQty = Quantity::of($nQty)->toInt();

        return $this;
    }

    public function getSpecialPriceCents(): int
    {
        return $this->specialPriceCents;
    }

    public function setSpecialPriceCents(int $specialPriceCents): self
    {
        $this->specialPriceCents = $specialPriceCents;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getNQuantity(): Quantity
    {
        return Quantity::of($this->nQty);
    }
}
