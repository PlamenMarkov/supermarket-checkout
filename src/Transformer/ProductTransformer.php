<?php

namespace App\Transformer;

use App\Entity\Product;

class ProductTransformer extends AbstractTransformer
{
    public function toArray(Product $product, bool $withChildren = true): array
    {
        $data = [
            'id' => $product->getId(),
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'unit_price_cents' => $product->getUnitPriceCents(),
        ];

        if ($withChildren) {
            $promos = [];
            foreach ($product->getPromotions() as $promotion) {
                $promos[] = [
                    'id' => $promotion->getId(),
                    'type' => $promotion->getType()->value,
                    'n_qty' => $promotion->getNQty(),
                    'special_price_cents' => $promotion->getSpecialPriceCents(),
                ];
            }
            $data['promotions'] = $promos;
        }

        return $data;
    }
}
