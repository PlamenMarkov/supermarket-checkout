<?php

namespace App\Services;

use App\Dto\ProductDto;
use App\Entity\Order;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Value\Sku;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly final class ProductService
{
    public function __construct(
        private ProductRepository   $products,
        private DtoValidatorService $dtoValidator,
    ) {}

    public function list(): array
    {
        return $this->products->findAllWithPromotions();
    }

    public function get(int $id): Product
    {
        $product = $this->products->find($id);
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        return $product;
    }

    public function getBySku(Sku $sku): ?Product
    {
        $product = $this->products->findOneBySku($sku->toString());
        if ($this->products->findOneBySku($sku->toString())) {
            throw new HttpException(Response::HTTP_CONFLICT, 'SKU already exists');
        }

        return $product;
    }

    public function create(ProductDto $dto): Product
    {
        $this->dtoValidator->assertValid($dto);

        $sku = Sku::fromString($dto->sku);

        $this->getBySku($sku);

        $product = (new Product())
            ->setSku($sku)
            ->setName($dto->name)
            ->setUnitPriceCents($dto->unitPriceCents)
            ->setUpdatedAt(new \DateTimeImmutable());
        $this->products->save($product, true);

        return $product;
    }

    public function update(int $id, ProductDto $dto): Product
    {
        $this->dtoValidator->assertValid($dto);

        $product = $this->get($id);

        $sku = Sku::fromString($dto->sku);

        if ($sku->toString() !== $product->getSku()) {
            $this->getBySku($sku);
            $product->setSku($sku);
        }

        $product->setName($dto->name);
        $product->setUnitPriceCents($dto->unitPriceCents);

        $product->setUpdatedAt(new \DateTimeImmutable());
        $this->products->save($product, true);

        return $product;
    }

    public function delete(int $id): void
    {
        $product = $this->get($id);
        $this->products->remove($product, true);
    }
}
