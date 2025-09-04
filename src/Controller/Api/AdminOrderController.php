<?php

namespace App\Controller\Api;

use App\Repository\OrderRepository;
use App\Services\OrderService;
use App\Transformer\OrderTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/orders')]
class AdminOrderController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderService $orderService,
        private readonly OrderTransformer $orderTransformer,
    ) {}

    #[Route('', name: 'admin_orders_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->json($this->orderTransformer->collection($this->orders->findAllWithItems()));
    }

    #[Route('/{id}', name: 'admin_orders_get_by_id', methods: ['GET'])]
    public function getOrderById(int $id): Response
    {
        $order = $this->orders->findWithItems($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->orderTransformer->toArray($order));
    }

    #[Route('/{id}/complete', name: 'admin_orders_complete', methods: ['POST'])]
    public function complete(int $id): Response
    {
        $this->orderService->complete($id);
        $order = $this->orders->find($id);

        return $this->json($order ? $this->orderTransformer->toArray($order, false) : null);
    }

    #[Route('/{id}/cancel', name: 'admin_orders_cancel', methods: ['POST'])]
    public function cancel(int $id): Response
    {
        $this->orderService->cancel($id);
        $order = $this->orders->find($id);

        return $this->json($order ? $this->orderTransformer->toArray($order, false) : null);
    }
}
