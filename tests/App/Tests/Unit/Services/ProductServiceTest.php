<?php

namespace App\Tests\Unit\Services;

use App\Dto\ProductDto;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Services\DtoValidatorService;
use App\Services\ProductService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductServiceTest extends TestCase
{
    public function testCreateSuccess(): void
    {
        $repo = $this->createMock(ProductRepository::class);
        $repo->method('findOneBySku')->with('A')->willReturn(null);
        $repo->expects($this->once())->method('save')->with($this->isInstanceOf(Product::class), true);

        $productService = new ProductService($repo, $this->dtoValidator());
        $product = $productService->create(new ProductDto('A', 'Item A', 100));

        $this->assertSame('A', $product->getSku());
        $this->assertSame('Item A', $product->getName());
        $this->assertSame(100, $product->getUnitPriceCents());
    }

    public function testCreateDuplicateSkuThrowsException(): void
    {
        $repo = $this->createMock(ProductRepository::class);
        $repo->method('findOneBySku')->with('A')->willReturn(new Product());

        $productService = new ProductService($repo, $this->dtoValidator());
        $this->expectException(HttpException::class);
        $productService->create(new ProductDto('A', 'Item A', 100));
    }

    public function testUpdateSuccessChangeNameAndPrice(): void
    {
        $existing = (new Product())->setSku('A')->setName('Old')->setUnitPriceCents(50);

        $repo = $this->createMock(ProductRepository::class);
        $repo->method('find')->with(5)->willReturn($existing);
        $repo->method('findOneBySku')->willReturn(null);
        $repo->expects($this->once())->method('save')->with($existing, true);

        $productService = new ProductService($repo, $this->dtoValidator());
        $updated = $productService->update(5, new ProductDto('A', 'New Name', 200));

        $this->assertSame('A', $updated->getSku());
        $this->assertSame('New Name', $updated->getName());
        $this->assertSame(200, $updated->getUnitPriceCents());
    }

    public function testUpdateNotFoundThrowsException(): void
    {
        $repo = $this->createMock(ProductRepository::class);
        $repo->method('find')->with(99)->willReturn(null);

        $productService = new ProductService($repo, $this->dtoValidator());
        $this->expectException(HttpException::class);
        $productService->update(99, new ProductDto('A', 'Item A', 1));
    }

    public function testUpdateChangeSkuToExistingThrowsException(): void
    {
        $existing = (new Product())->setSku('A')->setName('Old')->setUnitPriceCents(50);

        $repo = $this->createMock(ProductRepository::class);
        $repo->method('find')->with(5)->willReturn($existing);
        $repo->method('findOneBySku')->with('B')->willReturn(new Product());

        $productService = new ProductService($repo, $this->dtoValidator());
        $this->expectException(HttpException::class);
        $productService->update(5, new ProductDto('B', 'Old', 50));
    }

    public function testUpdateWithInvalidSkuThrowsException(): void
    {
        $existing = (new Product())->setSku('A')->setName('Old')->setUnitPriceCents(50);

        $repo = $this->createMock(ProductRepository::class);
        $repo->method('find')->with(5)->willReturn($existing);

        $productService = new ProductService($repo, $this->dtoValidator());
        $this->expectException(\InvalidArgumentException::class);
        $productService->update(5, new ProductDto('AA', 'Old', 50));
    }

    private function dtoValidator(): DtoValidatorService
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList());

        return new DtoValidatorService($validator);
    }
}
