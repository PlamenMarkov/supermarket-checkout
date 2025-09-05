<?php

namespace App\Tests\Unit\Transformer;

use App\Entity\Product;
use App\Entity\Promotion;
use App\Enum\PromotionType;
use App\Transformer\PromotionTransformer;
use PHPUnit\Framework\TestCase;

class PromotionTransformerTest extends TestCase
{
    private function makePromotion(): Promotion
    {
        $product = (new Product())->setSku('A')->setName('Item A')->setUnitPriceCents(100);

        $promotion = new Promotion();
        $promotion->setProduct($product)
            ->setType(PromotionType::N_FOR_PRICE)
            ->setNQty(3)
            ->setSpecialPriceCents(250);

        return $promotion;
    }

    public function testToArrayWithChildren(): void
    {
        $promotion = $this->makePromotion();
        $transformer = new PromotionTransformer();
        $arr = $transformer->toArray($promotion);

        $this->assertSame('n_for_price', $arr['type']);
        $this->assertSame(3, $arr['n_qty']);
        $this->assertSame(250, $arr['special_price_cents']);
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertArrayHasKey('updated_at', $arr);
        $this->assertIsArray($arr['product']);
        $this->assertSame('A', $arr['product']['sku']);
        $this->assertSame('Item A', $arr['product']['name']);
        $this->assertSame(100, $arr['product']['unit_price_cents']);
    }

    public function testToArrayWithoutChildren(): void
    {
        $promotion = $this->makePromotion();
        $transformer = new PromotionTransformer();
        $arr = $transformer->toArray($promotion, false);

        $this->assertArrayNotHasKey('product', $arr);
    }
}
