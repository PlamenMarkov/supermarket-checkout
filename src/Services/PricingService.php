<?php

namespace App\Services;

use App\Entity\OrderItem;
use App\Entity\Promotion;
use App\Repository\PromotionRepository;
use App\Value\Money;
use App\Value\Quantity;

readonly final class PricingService
{
    public function __construct(private PromotionRepository $promotions) {}

    public function applyPricing(OrderItem $item): void
    {
        $qty = $item->getQty();
        $unit = $item->getUnitPrice();
        $product = $item->getProduct();
        $finalTotal = $unit->times($qty->toInt());
        $finalBundleCount = 0;
        $finalBundlePrice = null;

        if ($product) {
            $promotions = $this->promotions->findByProduct($product);
            foreach ($promotions as $promotion) {
                list($bundles, $bundlePrice, $total) = $this->calculateItemFinals($promotion, $qty, $unit);

                if ($total->isLessThan($finalTotal)) {
                    $finalTotal = $total;
                    $finalBundleCount = $bundles;
                    $finalBundlePrice = $bundlePrice;
                }
            }
        }

        $item->setBundleCount($finalBundleCount)
            ->setBundlePrice($finalBundlePrice)
            ->setLineTotal($finalTotal);
    }

    private function calculateItemFinals(Promotion $promotion, Quantity $qty, Money $unit): array
    {
        $nQty = $promotion->getNQuantity()->toInt();
        $bundles = intdiv($qty->toInt(), $nQty);
        $remainder = $qty->toInt() % $nQty;
        $bundlePrice = $promotion->getSpecialPrice();
        $bundlePrice = Money::ofCents($bundlePrice->getCents());
        $totalForBundles = $bundlePrice->times($bundles);
        $totalForRemainder = $unit->times($remainder);
        $total = $totalForBundles->plus($totalForRemainder);

        return array($bundles, $bundlePrice, $total);
    }
}
