<?php

namespace App\Services;

use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;

readonly final class OrderService
{
    public function __construct(private OrderRepository $orders) {}

    public function complete(int $id): void
    {
        $order = $this->orders->find($id);
        if (!$order) {
            throw new HttpException(404, 'Order not found');
        }

        if ($order->getStatus() !== OrderStatus::CREATED) {
            throw new HttpException(400, 'Invalid transition');
        }

        $order->setStatus(OrderStatus::COMPLETED)->setUpdatedAt(new \DateTimeImmutable());
        $this->orders->save($order, true);
    }

    public function cancel(int $id): void
    {
        $order = $this->orders->find($id);
        if (!$order) {
            throw new HttpException(404, 'Order not found');
        }

        if ($order->getStatus() !== OrderStatus::CREATED) {
            throw new HttpException(400, 'Invalid transition');
        }

        $order->setStatus(OrderStatus::CANCELLED)->setUpdatedAt(new \DateTimeImmutable());
        $this->orders->save($order, true);
    }
}
