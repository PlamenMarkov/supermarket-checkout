<?php

namespace App\Controller\Api;

use App\Services\OrderService;
use App\Transformer\OrderTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/orders')]
class AdminOrderController extends AbstractController
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly OrderTransformer $orderTransformer,
    ) {}

    #[Route('', name: 'admin_orders_list', methods: ['GET'])]
    public function index(): Response
    {
        return $this->json($this->orderTransformer->collection($this->orderService->list()));
    }

    #[Route('/{id}', name: 'admin_orders_get_by_id', methods: ['GET'])]
    public function getOrderById(int $id): Response
    {
        return $this->json($this->orderTransformer->toArray($this->orderService->getWithItems($id)));
    }

    #[Route('/{id}/complete', name: 'admin_orders_complete', methods: ['POST'])]
    public function complete(int $id): Response
    {
        $order = $this->orderService->complete($id);

        return $this->json($this->orderTransformer->toArray($order, false));
    }

    #[Route('/{id}/cancel', name: 'admin_orders_cancel', methods: ['POST'])]
    public function cancel(int $id): Response
    {
        $order = $this->orderService->cancel($id);

        return $this->json($this->orderTransformer->toArray($order, false));
    }
}
