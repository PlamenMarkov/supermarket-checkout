<?php

namespace App\Services;

use App\Dto\ProductDto;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Value\Sku;
use Symfony\Component\HttpKernel\Exception\HttpException;

readonly final class ProductService
{
    public function __construct(
        private ProductRepository   $products,
        private DtoValidatorService $dtoValidator,
    ) {}

    public function create(ProductDto $dto): Product
    {
        $this->dtoValidator->assertValid($dto);

        $sku = Sku::fromString($dto->sku);

        if ($this->products->findOneBySku($sku->toString())) {
            throw new HttpException(409, 'SKU already exists');
        }

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
        $product = $this->products->find($id);
        if (!$product) {
            throw new HttpException(404, 'Product not found');
        }

        $this->dtoValidator->assertValid($dto);

        $sku = Sku::fromString($dto->sku);

        if ($sku->toString() !== $product->getSku()) {
            if ($this->products->findOneBySku($sku->toString())) {
                throw new HttpException(409, 'SKU already exists');
            }
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
        $product = $this->products->find($id);
        if (!$product) {
            throw new HttpException(404, 'Product not found');
        }
        $this->products->remove($product, true);
    }
}
