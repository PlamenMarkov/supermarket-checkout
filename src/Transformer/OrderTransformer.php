<?php

namespace App\Transformer;

use App\Entity\Order;
use App\Entity\OrderItem;

class OrderTransformer extends AbstractTransformer
{
    public function itemToArray(OrderItem $item): array
    {
        return [
            'id' => $item->getId(),
            'sku' => $item->getSku(),
            'product_name' => $item->getProductName(),
            'qty' => $item->getQty()->toInt(),
            'unit_price_cents' => $item->getUnitPriceCents(),
            'bundle_count' => $item->getBundleCount(),
            'bundle_price_cents' => $item->getBundlePriceCents(),
            'line_total_cents' => $item->getLineTotalCents(),
            'currency' => $item->getCurrency()->value,
        ];
    }

    public function toArray(Order $order, bool $withChildren = true): array
    {
        $data = [
            'id' => $order->getId(),
            'status' => $order->getStatus()->value,
            'total_cents' => $order->getTotalCents(),
            'currency' => $order->getCurrency()->value,
            'created_at' => $order->getCreatedAt()->format('c'),
        ];

        if ($withChildren) {
            $items = [];
            foreach ($order->getItems() as $i) {
                $items[] = $this->itemToArray($i);
            }
            $data['items'] = $items;
        }

        return $data;
    }
}
