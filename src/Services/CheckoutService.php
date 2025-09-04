<?php

namespace App\Services;

use App\Dto\OrderDto;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Enum\OrderStatus;
use App\Repository\ProductRepository;
use App\Value\Money;
use App\Value\Quantity;
use App\Value\Sku;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly final class CheckoutService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PricingService         $pricing,
        private DtoValidatorService    $dtoValidator,
        private ProductRepository      $productRepository,
    ) {}

    public function createOrder(OrderDto $dto): Order
    {
        $this->dtoValidator->assertValid($dto);

        $skusCount = $dto->countSkus();

        $this->em->beginTransaction();

        try {
            return $this->persistOrder($skusCount);
        } catch (\Throwable $e) {
            $this->em->rollback();

            throw $e;
        }
    }

    private function persistOrder(array $skusCount): Order
    {
        $order = new Order();
        $this->em->persist($order);

        $total = Money::ofCents(0);
        foreach ($skusCount as $sku => $qty) {
            $total = $this->persistItem($sku, $order, $qty, $total);
        }

        $order->setTotalCents($total->getCents())
            ->setStatus(OrderStatus::CREATED)
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->em->flush();
        $this->em->commit();

        return $order;
    }

    private function persistItem(string $sku, Order $order, mixed $qty, mixed $total): Money
    {
        $skuObject = Sku::fromString($sku);

        $product = $this->productRepository->findOneBySku($sku);
        if (!$product) throw new NotFoundHttpException("Product not found: $sku");

        $item = OrderItem::build($order, $product, $skuObject, Quantity::of($qty));

        $this->pricing->applyPricing($item);

        $order->addItem($item);

        $total = $total->plus(Money::ofCents($item->getLineTotalCents()));

        $this->em->persist($item);

        return $total;
    }
}
