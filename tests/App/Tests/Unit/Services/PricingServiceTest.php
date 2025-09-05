<?php

namespace App\Tests\Unit\Services;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\Promotion;
use App\Enum\PromotionType;
use App\Repository\PromotionRepository;
use App\Services\PricingService;
use App\Value\Quantity;
use App\Value\Sku;
use PHPUnit\Framework\TestCase;

class PricingServiceTest extends TestCase
{
    public function testNoPromotionsUseUnitPricing(): void
    {
        $item = $this->makeItem(100, 4);

        $repo = $this->createMock(PromotionRepository::class);
        $repo->method('findByProduct')->willReturn([]);

        $pricingService = new PricingService($repo);
        $pricingService->applyPricing($item);

        $this->assertSame(0, $item->getBundleCount());
        $this->assertNull($item->getBundlePriceCents());
        $this->assertSame(400, $item->getLineTotalCents());
    }

    public function testSinglePromotionExactBundle(): void
    {
        $item = $this->makeItem(100, 4);
        $product = $item->getProduct();

        $repo = $this->createMock(PromotionRepository::class);
        $repo->method('findByProduct')->willReturn([
            $this->promo($product, 2, 150),
        ]);

        $pricingService = new PricingService($repo);
        $pricingService->applyPricing($item);

        $this->assertSame(2, $item->getBundleCount());
        $this->assertSame(150, $item->getBundlePriceCents());
        $this->assertSame(300, $item->getLineTotalCents());
    }

    public function testMultiplePromotionsChooseBest(): void
    {
        $item = $this->makeItem(100, 5);
        $product = $item->getProduct();

        $repo = $this->createMock(PromotionRepository::class);
        $repo->method('findByProduct')->willReturn([
            $this->promo($product, 3, 260),
            $this->promo($product, 2, 180),
        ]);

        $pricingService = new PricingService($repo);
        $pricingService->applyPricing($item);

        $this->assertSame(1, $item->getBundleCount());
        $this->assertSame(260, $item->getBundlePriceCents());
        $this->assertSame(460, $item->getLineTotalCents());
    }

    public function testPromotionThatIsNotAnActualPromotionIsIgnored(): void
    {
        $item = $this->makeItem(100, 1);
        $product = $item->getProduct();

        $repo = $this->createMock(PromotionRepository::class);
        $repo->method('findByProduct')->willReturn([
            $this->promo($product, 1, 120),
        ]);

        $pricingService = new PricingService($repo);
        $pricingService->applyPricing($item);

        $this->assertSame(0, $item->getBundleCount());
        $this->assertNull($item->getBundlePriceCents());
        $this->assertSame(100, $item->getLineTotalCents());
    }

    private function makeItem(int $unitPrice, int $qty): OrderItem
    {
        $order = new Order();
        $product = (new Product())->setSku('A')->setName('Apple')->setUnitPriceCents($unitPrice);

        return OrderItem::build($order, $product, Sku::fromString('A'), Quantity::of($qty));
    }

    private function promo(Product $product, int $n, int $price): Promotion
    {
        $promotion = new Promotion();
        $promotion->setProduct($product)->setType(PromotionType::N_FOR_PRICE)->setNQty($n)->setSpecialPriceCents($price);

        return $promotion;
    }
}
