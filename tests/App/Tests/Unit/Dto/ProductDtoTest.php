<?php

namespace App\Tests\Unit\Dto;

use App\Dto\ProductDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ProductDtoTest extends TestCase
{
    public function testFromArray(): void
    {
        $dto = ProductDto::fromArray([
            'sku' => '  A   ',
            'name' => 'Item A',
            'unit_price_cents' => '123',
        ]);

        $this->assertSame('A', $dto->sku);
        $this->assertSame('Item A', $dto->name);
        $this->assertSame(123, $dto->unitPriceCents);
    }

    public function testValidationRules(): void
    {
        $dto = new ProductDto('', '', -1);
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $violations = $validator->validate($dto);
        $this->assertGreaterThan(0, count($violations));
    }
}
