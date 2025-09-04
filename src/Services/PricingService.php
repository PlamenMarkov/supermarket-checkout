<?php

namespace App\Services;

use App\Entity\OrderItem;
use App\Repository\PromotionRepository;

final readonly class PricingService
{
    public function __construct(private PromotionRepository $promotions) {}

    public function applyPricing(OrderItem $item): void
    {
        $qty = $item->getQty();
        $unit = $item->getUnitPriceCents();
        $product = $item->getProduct();
        $finalTotal = $qty->toInt() * $unit;
        $finalBundleCount = 0;
        $finalBundlePrice = null;

        if ($product) {
            $promotions = $this->promotions->findByProduct($product);
            foreach ($promotions as $pr) {
                $nQty = $pr->getNQuantity()->toInt();
                $bundles = intdiv($qty->toInt(), $nQty);
                $remainder = $qty->toInt() % $nQty;
                $total = $bundles * $pr->getSpecialPriceCents() + $remainder * $unit;
                if ($total < $finalTotal) {
                    $finalTotal = $total;
                    $finalBundleCount = $bundles;
                    $finalBundlePrice = $pr->getSpecialPriceCents();
                }
            }
        }

        $item->setBundleCount($finalBundleCount)
            ->setBundlePriceCents($finalBundlePrice)
            ->setLineTotalCents($finalTotal);
    }
}
