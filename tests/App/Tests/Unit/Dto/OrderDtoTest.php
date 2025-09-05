<?php

namespace App\Tests\Unit\Dto;

use App\Dto\OrderDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class OrderDtoTest extends TestCase
{
    public function testFromArrayAndCountSkus(): void
    {
        $dto = OrderDto::fromArray(['skus' => 'AABXA']);
        $this->assertSame(['A' => 3, 'B' => 1, 'X' => 1], $dto->countSkus());
    }

    public function testValidationRegex(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $valid = new OrderDto('ABC');
        $this->assertSame(0, count($validator->validate($valid)));

        $invalid = new OrderDto('A!');
        $this->assertGreaterThanOrEqual(1, count($validator->validate($invalid)));

        $invalid = new OrderDto('12-');
        $this->assertGreaterThanOrEqual(1, count($validator->validate($invalid)));
    }
}
