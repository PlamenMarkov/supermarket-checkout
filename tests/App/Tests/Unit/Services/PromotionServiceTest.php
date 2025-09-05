<?php

namespace App\Tests\Unit\Services;

use App\Dto\PromotionDto;
use App\Entity\Product;
use App\Entity\Promotion;
use App\Enum\PromotionType;
use App\Repository\ProductRepository;
use App\Repository\PromotionRepository;
use App\Services\DtoValidatorService;
use App\Services\PromotionService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PromotionServiceTest extends TestCase
{
    public function testCreateSuccess(): void
    {
        $promos = $this->createMock(PromotionRepository::class);
        $products = $this->createMock(ProductRepository::class);
        $product = (new Product())->setSku('A')->setName('A')->setUnitPriceCents(50);
        $products->method('find')->with(1)->willReturn($product);
        $promos->expects($this->once())->method('save')->with($this->isInstanceOf(Promotion::class), true);

        $promotionService = new PromotionService($promos, $products, $this->dtoValidator());
        $promo = $promotionService->create(new PromotionDto(1, 'n_for_price', 3, 130));

        $this->assertSame(PromotionType::N_FOR_PRICE, $promo->getType());
        $this->assertSame(3, $promo->getNQty());
        $this->assertSame(130, $promo->getSpecialPriceCents());
        $this->assertSame($product, $promo->getProduct());
    }

    public function testCreateInvalidProduct(): void
    {
        $promos = $this->createMock(PromotionRepository::class);
        $products = $this->createMock(ProductRepository::class);
        $products->method('find')->with(1)->willReturn(null);

        $promotionService = new PromotionService($promos, $products, $this->dtoValidator());
        $this->expectException(HttpException::class);
        $promotionService->create(new PromotionDto(1, 'n_for_price', 3, 130));
    }

    public function testCreateInvalidType(): void
    {
        $promos = $this->createMock(PromotionRepository::class);
        $products = $this->createMock(ProductRepository::class);
        $products->method('find')->with(1)->willReturn(new Product());

        $promotionService = new PromotionService($promos, $products, $this->dtoValidator());
        $this->expectException(HttpException::class);
        $promotionService->create(new PromotionDto(1, 'bogus', 3, 130));
    }

    public function testCreateInvalidQty(): void
    {
        $promos = $this->createMock(PromotionRepository::class);
        $products = $this->createMock(ProductRepository::class);
        $products->method('find')->with(1)->willReturn(new Product());

        $promotionService = new PromotionService($promos, $products, $this->dtoValidator());
        $this->expectException(\InvalidArgumentException::class);
        $promotionService->create(new PromotionDto(1, 'n_for_price', 0, 130));
    }

    public function testCreateInvalidPriceNegative(): void
    {
        $promos = $this->createMock(PromotionRepository::class);
        $products = $this->createMock(ProductRepository::class);
        $products->method('find')->with(1)->willReturn(new Product());

        $promotionService = new PromotionService($promos, $products, $this->dtoValidator());
        $this->expectException(HttpException::class);
        $promotionService->create(new PromotionDto(1, 'n_for_price', 3, -5));
    }

    public function testGetAndDeleteThrowNotFoundException(): void
    {
        $promos = $this->createMock(PromotionRepository::class);
        $products = $this->createMock(ProductRepository::class);
        $promos->method('find')->with(5)->willReturn(null);

        $promotionService = new PromotionService($promos, $products, $this->dtoValidator());
        $this->expectException(NotFoundHttpException::class);
        $promotionService->get(5);

        $this->expectException(NotFoundHttpException::class);
        $promotionService->delete(5);
    }

    public function testUpdateOptionalFields(): void
    {
        $productA = (new Product())->setSku('A')->setName('A')->setUnitPriceCents(50);
        $productB = (new Product())->setSku('B')->setName('B')->setUnitPriceCents(30);

        $promo = (new Promotion())->setProduct($productA)->setType(PromotionType::N_FOR_PRICE)->setNQty(3)->setSpecialPriceCents(130);

        $promos = $this->createMock(PromotionRepository::class);
        $promos->method('find')->with(10)->willReturn($promo);
        $promos->expects($this->once())->method('save')->with($promo, true);

        $products = $this->createMock(ProductRepository::class);
        $products->method('find')->willReturnCallback(function (int $id) use ($productA, $productB) {
            return $id === 2 ? $productB : ($id === 1 ? $productA : null);
        });

        $promotionService = new PromotionService($promos, $products, $this->dtoValidator());
        $updated = $promotionService->update(10, new PromotionDto(2, 'n_for_price', 2, 45));

        $this->assertSame($productB, $updated->getProduct());
        $this->assertSame(2, $updated->getNQty());
        $this->assertSame(45, $updated->getSpecialPriceCents());
    }

    private function dtoValidator(): DtoValidatorService
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList());

        return new DtoValidatorService($validator);
    }
}
