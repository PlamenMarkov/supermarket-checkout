<?php

namespace App\Controller\Api;

use App\Dto\ProductDto;
use App\Exceptions\ValidationException;
use App\Repository\ProductRepository;
use App\Services\ProductService;
use App\Transformer\ProductTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/products')]
class AdminProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly ProductService $productService,
        private readonly ProductTransformer $productTransformer,
    ) {}

    #[Route('', name: 'admin_products_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json($this->productTransformer->collection($this->products->findAllWithPromotions()));
    }

    #[Route('', name: 'admin_products_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        try {
            $dto = ProductDto::fromArray($request->toArray());
            $product = $this->productService->create($dto);

            return $this->json(
                $this->productTransformer->toArray($product, false),
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            return $this->json(
                $e->toArray(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    #[Route('/{id}', name: 'admin_products_get_by_id', methods: ['GET'])]
    public function getProductById(int $id): Response
    {
        $product = $this->products->find($id);
        if (!$product) {
            return $this->json(
                ['error' => 'Product not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json($this->productTransformer->toArray($product));
    }

    #[Route('/{id}', name: 'admin_products_update', methods: ['PUT'])]
    public function update(int $id, Request $request): Response
    {
        try {
            $dto = ProductDto::fromArray($request->toArray());
            $product = $this->productService->update($id, $dto);

            return $this->json($this->productTransformer->toArray($product, false));
        } catch (ValidationException $e) {
            return $this->json(
                $e->toArray(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    #[Route('/{id}', name: 'admin_products_delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $this->productService->delete($id);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
