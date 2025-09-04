<?php

namespace App\Value;

final class Sku
{
    public const string SINGLE_SKU_VALIDATION_REGEX = '/^[A-Z]$/';
    public const string CHECKOUT_SKU_VALIDATION_REGEX = '/^[A-Z]+$/';
    public const string CHECKOUT_SKU_VALIDATION_MESSAGE = 'SKU must be a one or multiple letters from A–Z';
    public const string SINGLE_SKU_VALIDATION_MESSAGE = 'Invalid SKU. Allowed values: A–Z.';
    private string $value;

    private function __construct(string $value)
    {
        $value = strtoupper(trim($value));
        if ($value === '' || strlen($value) !== 1 || !preg_match(self::SINGLE_SKU_VALIDATION_REGEX, $value)) {
            throw new \InvalidArgumentException(self::SINGLE_SKU_VALIDATION_MESSAGE);
        }
        $this->value = $value;
    }

    public static function fromString(string $raw): self
    {
        return new self($raw);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
