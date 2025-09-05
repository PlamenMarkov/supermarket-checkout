<?php

namespace App\Tests\Unit\Transformer;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Enum\OrderStatus;
use App\Transformer\OrderTransformer;
use App\Value\Quantity;
use App\Value\Sku;
use PHPUnit\Framework\TestCase;

class OrderTransformerTest extends TestCase
{
    public function testItemToArrayMapsAllFields(): void
    {
        $order = new Order();
        $item = $this->makeItem($order, 'A', 'Item A', 150, 3, 1, 250, 400);

        $transformer = new OrderTransformer();
        $arr = $transformer->itemToArray($item);

        $this->assertSame('A', $arr['sku']);
        $this->assertSame('Item A', $arr['product_name']);
        $this->assertSame(3, $arr['qty']);
        $this->assertSame(150, $arr['unit_price_cents']);
        $this->assertSame(1, $arr['bundle_count']);
        $this->assertSame(250, $arr['bundle_price_cents']);
        $this->assertSame(400, $arr['line_total_cents']);
        $this->assertSame('BGN', $arr['currency']);
    }

    public function testToArrayWithoutChildren(): void
    {
        $order = new Order();
        $order->setTotalCents(1234)->setStatus(OrderStatus::CREATED);

        $transformer = new OrderTransformer();
        $arr = $transformer->toArray($order, false);

        $this->assertArrayHasKey('status', $arr);
        $this->assertSame('created', $arr['status']);
        $this->assertSame(1234, $arr['total_cents']);
        $this->assertSame('BGN', $arr['currency']);
        $this->assertArrayNotHasKey('items', $arr);
    }

    public function testToArrayWithChildrenMultipleItems(): void
    {
        $order = new Order();
        $iA = $this->makeItem($order, 'A', 'Item A', 100, 3, 1, 250, 350);
        $iB = $this->makeItem($order, 'B', 'Item B', 50, 2, 1, 80, 130);
        $order->addItem($iA)->addItem($iB);
        $order->setTotalCents(480);

        $transformer = new OrderTransformer();
        $arr = $transformer->toArray($order);

        $this->assertSame('created', $arr['status']);
        $this->assertSame(480, $arr['total_cents']);
        $this->assertSame('BGN', $arr['currency']);
        $this->assertArrayHasKey('items', $arr);
        $this->assertIsArray($arr['items']);
        $this->assertCount(2, $arr['items']);

        $itemA = $arr['items'][0];
        $itemB = $arr['items'][1];

        $this->assertSame('A', $itemA['sku']);
        $this->assertSame(3, $itemA['qty']);
        $this->assertSame(1, $itemA['bundle_count']);
        $this->assertSame(250, $itemA['bundle_price_cents']);
        $this->assertSame(350, $itemA['line_total_cents']);

        $this->assertSame('B', $itemB['sku']);
        $this->assertSame(2, $itemB['qty']);
        $this->assertSame(1, $itemB['bundle_count']);
        $this->assertSame(80, $itemB['bundle_price_cents']);
        $this->assertSame(130, $itemB['line_total_cents']);
    }

    private function makeItem(Order $order, string $sku, string $name, int $unitPrice, int $qtyInt, int $bundleCount = 0, ?int $bundlePrice = null, int $lineTotal = 0): OrderItem
    {
        $product = (new Product())->setSku($sku)->setName($name)->setUnitPriceCents($unitPrice);
        $item = OrderItem::build($order, $product, Sku::fromString($sku), Quantity::of($qtyInt));
        $item->setBundleCount($bundleCount)
            ->setBundlePriceCents($bundlePrice)
            ->setLineTotalCents($lineTotal);

        return $item;
    }
}
