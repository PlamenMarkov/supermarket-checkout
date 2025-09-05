<?php

namespace App\Tests\Unit\Dto;

use App\Dto\PromotionDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class PromotionDtoTest extends TestCase
{
    public function testFromArray(): void
    {
        $dto = PromotionDto::fromArray([
            'product_id' => '1',
            'type' => 'n_for_price',
            'n_qty' => '3',
            'special_price_cents' => '450',
        ]);

        $this->assertSame(1, $dto->productId);
        $this->assertSame('n_for_price', $dto->type);
        $this->assertSame(3, $dto->nQty);
        $this->assertSame(450, $dto->specialPriceCents);
    }

    public function testValidationRules(): void
    {
        $dto = new PromotionDto(0, '', 0, -1);
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $violations = $validator->validate($dto);
        $this->assertGreaterThan(0, count($violations));
    }
}
