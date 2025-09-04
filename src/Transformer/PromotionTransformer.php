<?php

namespace App\Transformer;

use App\Entity\Promotion;

class PromotionTransformer extends AbstractTransformer
{
    public function toArray(Promotion $promotion, bool $withChildren = true): array
    {
        $data = [
            'id' => $promotion->getId(),
            'type' => $promotion->getType()->value,
            'n_qty' => $promotion->getNQty(),
            'special_price_cents' => $promotion->getSpecialPriceCents(),
            'created_at' => $promotion->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $promotion->getUpdatedAt()->format(DATE_ATOM),
        ];

        if ($withChildren) {
            $prod = $promotion->getProduct();
            $data['product'] = $prod ? [
                'id' => $prod->getId(),
                'sku' => $prod->getSku(),
                'name' => $prod->getName(),
                'unit_price_cents' => $prod->getUnitPriceCents(),
            ] : null;
        }

        return $data;
    }
}
