<?php

namespace App\Services;

use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly final class OrderService
{
    public function __construct(private OrderRepository $orders) {}

    public function list(): array
    {
        return $this->orders->findAllWithItems();
    }

    public function getWithItems(int $id): Order
    {
        $order = $this->orders->findWithItems($id);
        if (!$order) {
            throw new NotFoundHttpException('Order not found');
        }

        return $order;
    }

    public function get(int $id): Order
    {
        $order = $this->orders->find($id);
        if (!$order) {
            throw new NotFoundHttpException('Order not found');
        }

        return $order;
    }

    public function complete(int $id): Order
    {
        $order = $this->get($id);

        if ($order->getStatus() !== OrderStatus::CREATED) {
            throw new HttpException(400, 'Invalid transition');
        }

        $order->setStatus(OrderStatus::COMPLETED)->setUpdatedAt(new \DateTimeImmutable());
        $this->orders->save($order, true);

        return $order;
    }

    public function cancel(int $id): Order
    {
        $order = $this->get($id);

        if ($order->getStatus() !== OrderStatus::CREATED) {
            throw new HttpException(400, 'Invalid transition');
        }

        $order->setStatus(OrderStatus::CANCELLED)->setUpdatedAt(new \DateTimeImmutable());
        $this->orders->save($order, true);

        return $order;
    }


}
