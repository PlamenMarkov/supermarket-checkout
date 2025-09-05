<?php

namespace App\Tests\Unit\Value;

use App\Enum\Currency;
use App\Value\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function testFactoryAndAccessors(): void
    {
        $money = Money::ofCents(1234);
        $this->assertSame(1234, $money->getCents());
        $this->assertSame(Currency::BGN, $money->getCurrency());
    }

    public function testPlusWithSameCurrencyReturnsNewInstanceAndDoesNotMutate(): void
    {
        $a = Money::ofCents(100);
        $b = Money::ofCents(50);
        $c = $a->plus($b);

        $this->assertNotSame($a, $c);
        $this->assertSame(100, $a->getCents(), 'Original should be immutable');
        $this->assertSame(150, $c->getCents());
        $this->assertSame(Currency::BGN, $c->getCurrency());
    }

    public function testMinusWithSameCurrencyReturnsNewInstance(): void
    {
        $a = Money::ofCents(100);
        $b = Money::ofCents(40);
        $c = $a->minus($b);

        $this->assertSame(60, $c->getCents());
        $this->assertSame(100, $a->getCents(), 'Original should be immutable');
    }

    public function testTimesWithNonNegativeMultiplier(): void
    {
        $m = Money::ofCents(125);
        $zero = $m->times(0);
        $one = $m->times(1);
        $five = $m->times(5);

        $this->assertSame(0, $zero->getCents());
        $this->assertSame(125, $one->getCents());
        $this->assertSame(625, $five->getCents());
        $this->assertSame(125, $m->getCents(), 'Original should be immutable');
    }

    public function testTimesWithNegativeMultiplierThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Multiplier must be >= 0');
        Money::ofCents(100)->times(-1);
    }

    public function testCurrencyMismatchOnPlusMinusAndCompare(): void
    {
        $a = Money::ofCents(100);
        $b = Money::ofCents(50);
        $c = Money::ofCents(30);

        $this->assertSame(150, $a->plus($b)->getCents());
        $this->assertSame(70, $a->minus($c)->getCents());

        $this->assertFalse($a->isLessThan($b), '100 is not less than 50');
        $this->assertTrue($b->isLessThan($a), '50 is less than 100');
    }

    public function testEquals(): void
    {
        $a1 = Money::ofCents(200);
        $a2 = Money::ofCents(200);
        $b = Money::ofCents(201);

        $this->assertTrue($a1->equals($a2));
        $this->assertFalse($a1->equals($b));
    }

    public function testMin(): void
    {
        $a = Money::ofCents(100);
        $b = Money::ofCents(200);

        $this->assertSame(100, Money::min($a, $b)->getCents());
        $this->assertSame(100, Money::min($b, $a)->getCents());
    }

    public function testToStringFormatting(): void
    {
        $zero = Money::ofCents(0);
        $oneTwentyThree = Money::ofCents(123);
        $ten = Money::ofCents(1000);

        $this->assertSame('0.00 BGN', (string)$zero);
        $this->assertSame('1.23 BGN', (string)$oneTwentyThree);
        $this->assertSame('10.00 BGN', (string)$ten);
    }
}
