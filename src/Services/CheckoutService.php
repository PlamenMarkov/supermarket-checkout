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

    public function checkoutOrder(OrderDto $dto): Order
    {
        $this->dtoValidator->assertValid($dto);

        $skusCount = $dto->countSkus();

        $this->em->beginTransaction();

        try {
            $order = $this->createOrder($skusCount);
            $this->em->persist($order);
            $this->em->flush();
            $this->em->commit();

            return $order;
        } catch (\Throwable $e) {
            $this->em->rollback();

            throw $e;
        }
    }

    private function createOrder(array $skusCount): Order
    {
        $order = new Order();

        $total = Money::ofCents(0);
        foreach ($skusCount as $sku => $qty) {
            $total = $this->createOrderItem($sku, $order, $qty, $total);
        }

        $order->setTotalCents($total->getCents())
            ->setStatus(OrderStatus::CREATED)
            ->setUpdatedAt(new \DateTimeImmutable());

        return $order;
    }

    private function createOrderItem(string $sku, Order $order, int $qty, Money $total): Money
    {
        $skuObject = Sku::fromString($sku);

        $product = $this->productRepository->findOneBySku($sku);
        if (!$product) throw new NotFoundHttpException("Product not found: $sku");

        $item = OrderItem::build($order, $product, $skuObject, Quantity::of($qty));

        $this->pricing->applyPricing($item);

        $order->addItem($item);

        return $total->plus(Money::ofCents($item->getLineTotalCents()));
    }
}
