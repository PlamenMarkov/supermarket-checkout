<?php

namespace App\Tests\Unit\Services;

use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\Services\OrderService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderServiceTest extends TestCase
{
    public function testCompleteSuccess(): void
    {
        $order = new Order();
        $repo = $this->createMock(OrderRepository::class);
        $repo->method('find')->with(1)->willReturn($order);
        $repo->expects($this->once())->method('save')->with($order, true);

        $orderService = new OrderService($repo);
        $orderService->complete(1);

        $this->assertSame(OrderStatus::COMPLETED, $order->getStatus());
    }

    public function testCompleteNotFound(): void
    {
        $repo = $this->createMock(OrderRepository::class);
        $repo->method('find')->with(1)->willReturn(null);

        $orderService = new OrderService($repo);
        $this->expectException(HttpException::class);
        $orderService->complete(1);
    }

    public function testCompleteInvalidTransition(): void
    {
        $order = new Order();
        $order->setStatus(OrderStatus::COMPLETED);
        $repo = $this->createMock(OrderRepository::class);
        $repo->method('find')->with(1)->willReturn($order);

        $orderService = new OrderService($repo);
        $this->expectException(HttpException::class);
        $orderService->complete(1);
    }

    public function testCancelSuccess(): void
    {
        $order = new Order();
        $repo = $this->createMock(OrderRepository::class);
        $repo->method('find')->with(2)->willReturn($order);
        $repo->expects($this->once())->method('save')->with($order, true);

        $orderService = new OrderService($repo);
        $orderService->cancel(2);

        $this->assertSame(OrderStatus::CANCELLED, $order->getStatus());
    }

    public function testCancelInvalidTransition(): void
    {
        $order = new Order();
        $order->setStatus(OrderStatus::COMPLETED);
        $repo = $this->createMock(OrderRepository::class);
        $repo->method('find')->with(2)->willReturn($order);

        $orderService = new OrderService($repo);
        $this->expectException(HttpException::class);
        $orderService->cancel(2);
    }
}
