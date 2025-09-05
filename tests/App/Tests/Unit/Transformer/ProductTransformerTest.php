<?php

namespace App\Tests\Unit\Transformer;

use App\Entity\Product;
use App\Entity\Promotion;
use App\Enum\PromotionType;
use App\Transformer\ProductTransformer;
use PHPUnit\Framework\TestCase;

class ProductTransformerTest extends TestCase
{
    public function testToArrayWithoutChildren(): void
    {
        $product = (new Product())
            ->setSku('A')
            ->setName('Item A')
            ->setUnitPriceCents(100);

        $transformer = new ProductTransformer();
        $arr = $transformer->toArray($product, false);

        $this->assertSame('A', $arr['sku']);
        $this->assertSame('Item A', $arr['name']);
        $this->assertSame(100, $arr['unit_price_cents']);
        $this->assertArrayNotHasKey('promotions', $arr);
    }

    public function testToArrayWithChildrenEmptyPromotions(): void
    {
        $product = (new Product())
            ->setSku('B')
            ->setName('Item B')
            ->setUnitPriceCents(200);

        $transformer = new ProductTransformer();
        $arr = $transformer->toArray($product);

        $this->assertArrayHasKey('promotions', $arr);
        $this->assertIsArray($arr['promotions']);
        $this->assertCount(0, $arr['promotions']);
    }

    public function testToArrayWithChildrenWithPromotions(): void
    {
        $product = (new Product())
            ->setSku('C')
            ->setName('Item C')
            ->setUnitPriceCents(300);

        $promotion1 = (new Promotion())
            ->setProduct($product)
            ->setType(PromotionType::N_FOR_PRICE)
            ->setNQty(3)
            ->setSpecialPriceCents(700);
        $promotion2 = (new Promotion())
            ->setProduct($product)
            ->setType(PromotionType::N_FOR_PRICE)
            ->setNQty(2)
            ->setSpecialPriceCents(500);

        $product->getPromotions()->add($promotion1);
        $product->getPromotions()->add($promotion2);

        $transformer = new ProductTransformer();
        $arr = $transformer->toArray($product);

        $this->assertArrayHasKey('promotions', $arr);
        $this->assertCount(2, $arr['promotions']);

        $first = $arr['promotions'][0];
        $second = $arr['promotions'][1];

        $this->assertSame('n_for_price', $first['type']);
        $this->assertSame(3, $first['n_qty']);
        $this->assertSame(700, $first['special_price_cents']);

        $this->assertSame('n_for_price', $second['type']);
        $this->assertSame(2, $second['n_qty']);
        $this->assertSame(500, $second['special_price_cents']);
    }
}
