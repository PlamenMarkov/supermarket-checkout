<?php

namespace App\Tests\Unit\Value;

use App\Value\Quantity;
use PHPUnit\Framework\TestCase;

class QuantityTest extends TestCase
{
    public function testOfWithValidValue(): void
    {
        $q = Quantity::of(3);
        $this->assertSame(3, $q->toInt());
    }

    public function testOfThrowsOnZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Quantity::of(0);
    }

    public function testOfThrowsOnNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Quantity::of(-5);
    }
}
