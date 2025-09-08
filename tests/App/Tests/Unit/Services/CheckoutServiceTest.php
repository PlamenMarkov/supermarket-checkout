<?php

namespace App\Tests\Unit\Services;

use App\Dto\OrderDto;
use App\Entity\Product;
use App\Exceptions\ValidationException;
use App\Repository\ProductRepository;
use App\Repository\PromotionRepository;
use App\Services\CheckoutService;
use App\Services\DtoValidatorService;
use App\Services\PricingService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckoutServiceTest extends TestCase
{
    public function testCreateOrderSuccess(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('beginTransaction');
        $em->expects($this->once())->method('commit');
        $em->expects($this->once())->method('flush');
        $em->method('persist');

        $products = $this->createMock(ProductRepository::class);
        $pricing = new PricingService($this->createMock(PromotionRepository::class));

        $dto = new OrderDto('AAB');
        $productA = (new Product())->setSku('A')->setName('Apple')->setUnitPriceCents(100);
        $productB = (new Product())->setSku('B')->setName('Banana')->setUnitPriceCents(200);
        $products->method('findOneBySku')->willReturnCallback(function (string $sku) use ($productA, $productB) {
            return $sku === 'A' ? $productA : ($sku === 'B' ? $productB : null);
        });

        $checkoutService = new CheckoutService($em, $pricing, $this->dtoValidatorOk(), $products);
        $order = $checkoutService->checkoutOrder($dto);

        $this->assertSame(400, $order->getTotalCents());
        $this->assertCount(2, $order->getItems());
    }

    public function testCreateOrderThrowsValidationException(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $products = $this->createMock(ProductRepository::class);
        $pricing = new PricingService($this->createMock(PromotionRepository::class));

        $checkoutService = new CheckoutService($em, $pricing, $this->dtoValidatorWithViolations(1), $products);
        $this->expectException(ValidationException::class);
        $checkoutService->checkoutOrder(new OrderDto('!!'));
    }

    public function testCreateOrderThrowsProductNotFoundException(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('beginTransaction');
        $em->expects($this->once())->method('rollback');
        $products = $this->createMock(ProductRepository::class);
        $products->method('findOneBySku')->willReturn(null);
        $pricing = new PricingService($this->createMock(PromotionRepository::class));

        $checkoutService = new CheckoutService($em, $pricing, $this->dtoValidatorOk(), $products);
        $this->expectException(NotFoundHttpException::class);
        $checkoutService->checkoutOrder(new OrderDto('Z'));
    }

    private function dtoValidatorWithViolations(int $count): DtoValidatorService
    {
        $violations = $this->getMockBuilder(ConstraintViolationList::class)->disableOriginalConstructor()->getMock();
        $violations->method('count')->willReturn($count);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn($violations);

        return new DtoValidatorService($validator);
    }

    private function dtoValidatorOk(): DtoValidatorService
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList());

        return new DtoValidatorService($validator);
    }
}
