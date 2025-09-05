<?php

namespace App\Value;

use App\Enum\Currency;

final class Money
{
    private int $cents;
    private Currency $currency;

    private function __construct(int $cents, ?Currency $currency = Currency::BGN)
    {
        $this->cents = $cents;
        $this->currency = $currency;
    }

    public static function ofCents(int $cents, ?Currency $currency = Currency::BGN): self
    {
        return new self($cents, $currency);
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getCents(): int
    {
        return $this->cents;
    }

    public function plus(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->cents + $other->cents, $this->currency);
        
    }

    public function minus(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->cents - $other->cents, $this->currency);
    }

    public function times(int $multiplier): self
    {
        if ($multiplier < 0) {
            throw new \InvalidArgumentException('Multiplier must be >= 0');
        }
        return new self($this->cents * $multiplier, $this->currency);
    }

    public function isLessThan(self $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->cents < $other->cents;
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency && $this->cents === $other->cents;
    }

    public static function min(self $a, self $b): self
    {
        $a->assertSameCurrency($b);

        return $a->cents <= $b->cents ? $a : $b;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Currency mismatch.');
        }
    }

    public function __toString(): string
    {
        $amount = number_format($this->cents / 100, 2, '.', '');
        return $amount . ' ' . $this->currency->value;
    }
}
