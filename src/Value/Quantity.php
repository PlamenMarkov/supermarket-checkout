<?php

namespace App\Value;

final class Quantity
{
    private int $value;

    private function __construct(int $value)
    {
        if ($value < 1) {
            throw new \InvalidArgumentException('Quantity must be >= 1.');
        }
        $this->value = $value;
    }

    public static function of(int $value): self
    {
        return new self($value);
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
