<?php

namespace App\Entity;

use App\Enum\Currency;
use App\Repository\OrderItemRepository;
use App\Value\Money;
use App\Value\Quantity;
use App\Value\Sku;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'order_items')]
#[ORM\Index(name: 'idx_order_id', columns: ['order_id'])]
#[ORM\Index(name: 'idx_product_id', columns: ['product_id'])]
#[ORM\Index(name: 'idx_sku', columns: ['sku'])]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Order $order = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'orderItems')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Product $product = null;

    #[ORM\Column(type: 'string', length: 64)]
    private string $sku;

    #[ORM\Column(name: 'product_name', type: 'string', length: 255)]
    private string $productName;

    #[ORM\Column(name: 'qty', type: 'integer')]
    private int $qty;

    #[ORM\Column(name: 'unit_price_cents', type: 'integer')]
    private int $unitPriceCents;

    #[ORM\Column(name: 'bundle_count', type: 'integer')]
    private int $bundleCount;

    #[ORM\Column(name: 'bundle_price_cents', type: 'integer', nullable: true)]
    private ?int $bundlePriceCents = null;

    #[ORM\Column(name: 'line_total_cents', type: 'integer')]
    private int $lineTotalCents;

    #[ORM\Column(type: 'string', length: 3, enumType: Currency::class)]
    private Currency $currency;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->qty = 1;
        $this->bundleCount = 0;
        $this->lineTotalCents = 0;
        $this->currency = Currency::BGN;
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function build(Order $order, Product $product, Sku $sku, Quantity $qty): self
    {
        $item = new self();
        $item->setOrder($order)
            ->setProduct($product)
            ->setSku($sku)
            ->setProductName($product->getName())
            ->setQty($qty)
            ->setUnitPriceCents($product->getUnitPriceCents())
            ->setCurrency($order->getCurrency());

        return $item;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
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

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string|Sku $sku): self
    {
        $this->sku = $sku instanceof Sku ? $sku->toString() : $sku;

        return $this;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;
        return $this;
    }

    public function getQty(): Quantity
    {
        return Quantity::of($this->qty);
    }

    public function setQty(int|Quantity $qty): self
    {
        $this->qty = $qty instanceof Quantity ? $qty->toInt() : $qty;

        return $this;
    }

    public function getUnitPriceCents(): int
    {
        return $this->unitPriceCents;
    }

    public function getUnitPrice(): Money
    {
        return Money::ofCents($this->unitPriceCents, $this->currency);
    }

    public function setUnitPriceCents(int $unitPriceCents): self
    {
        $this->unitPriceCents = $unitPriceCents;

        return $this;
    }

    public function setUnitPrice(Money $money): self
    {
        $this->validateCurrency($money);
        $this->unitPriceCents = $money->getCents();

        return $this;
    }

    public function getBundleCount(): int
    {
        return $this->bundleCount;
    }

    public function setBundleCount(int $bundleCount): self
    {
        $this->bundleCount = $bundleCount;

        return $this;
    }

    public function getBundlePriceCents(): ?int
    {
        return $this->bundlePriceCents;
    }

    public function setBundlePriceCents(?int $bundlePriceCents): self
    {
        $this->bundlePriceCents = $bundlePriceCents;

        return $this;
    }

    public function setBundlePrice(?Money $money): self
    {
        if ($money === null) {
            $this->bundlePriceCents = null;
            return $this;
        }
        $this->validateCurrency($money);
        $this->bundlePriceCents = $money->getCents();

        return $this;
    }

    public function getLineTotalCents(): int
    {
        return $this->lineTotalCents;
    }

    public function getLineTotal(): Money
    {
        return Money::ofCents($this->lineTotalCents, $this->currency);
    }

    public function setLineTotalCents(int $lineTotalCents): self
    {
        $this->lineTotalCents = $lineTotalCents;

        return $this;
    }

    public function setLineTotal(Money $money): self
    {
        $this->validateCurrency($money);
        $this->lineTotalCents = $money->getCents();
        return $this;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    private function validateCurrency(Money $money): void
    {
        if ($money->getCurrency() !== $this->currency) {
            throw new \InvalidArgumentException('Currency mismatch for bundle price');
        }
    }
}
