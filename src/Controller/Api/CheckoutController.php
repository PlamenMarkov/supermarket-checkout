<?php

namespace App\Controller\Api;

use App\Dto\OrderDto;
use App\Exceptions\ValidationException;
use App\Services\CheckoutService;
use App\Transformer\OrderTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/checkout')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly OrderTransformer $orderTransformer,
    ) {}

    #[Route('/orders', name: 'checkout_create_order', methods: ['POST'])]
    public function createOrder(Request $request): JsonResponse
    {
        try {
            $dto = OrderDto::fromArray($request->toArray());
            $order = $this->checkout->createOrder($dto);

            return $this->json(
                $this->orderTransformer->toArray($order),
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            return $this->json(
                $e->toArray(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}
