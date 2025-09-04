<?php

namespace App\Services;

use App\Dto\OrderDto;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Enum\OrderStatus;
use App\Repository\ProductRepository;
use App\Value\Quantity;
use App\Value\Sku;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class CheckoutService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ProductRepository      $products,
        private PricingService         $pricing,
        private DtoValidatorService    $dtoValidator,
    ) {}

    public function createOrder(OrderDto $dto): Order
    {
        $this->dtoValidator->assertValid($dto);

        $skuCounts = $dto->countSkus();

        $this->em->beginTransaction();

        try {
            $order = new Order();
            $this->em->persist($order);

            $total = 0;
            foreach ($skuCounts as $skuStr => $qtyInt) {
                $product = $this->products->findOneBySku($skuStr);
                if (!$product) throw new NotFoundHttpException("Product not found: $skuStr");

                $item = OrderItem::build($order, $product, Sku::fromString($skuStr), Quantity::of($qtyInt));
                $this->pricing->applyPricing($item);
                $order->addItem($item);
                $total += $item->getLineTotalCents();
                $this->em->persist($item);
            }

            $order->setTotalCents($total)
                ->setStatus(OrderStatus::CREATED)
                ->setUpdatedAt(new \DateTimeImmutable());

            $this->em->flush();
            $this->em->commit();

            return $order;
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}
