<?php

namespace App\Controller\Api;

use App\Dto\PromotionDto;
use App\Exceptions\ValidationException;
use App\Services\PromotionService;
use App\Transformer\PromotionTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/promotions')]
class AdminPromotionController extends AbstractController
{
    public function __construct(
        private readonly PromotionService $promotionService,
        private readonly PromotionTransformer $transformer,
    ) {}

    #[Route('', name: 'admin_promotions_list', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json($this->transformer->collection($this->promotionService->list()));
    }

    #[Route('/{id}', name: 'admin_promotions_get_by_id', methods: ['GET'])]
    public function getPromotionById(int $id): JsonResponse
    {
        return $this->json($this->transformer->toArray($this->promotionService->get($id)));
    }

    #[Route('', name: 'admin_promotions_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $dto = PromotionDto::fromArray($request->toArray());
            $promo = $this->promotionService->create($dto);

            return $this->json($this->transformer->toArray($promo), Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->json(
                $e->toArray(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    #[Route('/{id}', name: 'admin_promotions_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $dto = PromotionDto::fromArray($request->toArray());
            $promo = $this->promotionService->update($id, $dto);

            return $this->json($this->transformer->toArray($promo));
        } catch (ValidationException $e) {
            return $this->json(
                $e->toArray(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    #[Route('/{id}', name: 'admin_promotions_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->promotionService->delete($id);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
