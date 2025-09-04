<?php

namespace App\Dto;

use App\Value\Sku;
use Symfony\Component\Validator\Constraints as Assert;

readonly final class ProductDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 1)]
        #[Assert\Regex(pattern: Sku::SINGLE_SKU_VALIDATION_REGEX, message: Sku::SINGLE_SKU_VALIDATION_MESSAGE)]
        public string $sku,

        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 50)]
        public string $name,

        #[Assert\NotNull]
        #[Assert\Type('integer')]
        #[Assert\GreaterThanOrEqual(0)]
        public int $unitPriceCents,
    ) {}

    public static function fromArray(array $data): self
    {
        $sku = trim((string)($data['sku'] ?? ''));
        $name = trim((string)($data['name'] ?? ''));
        $price = $data['unit_price_cents'] ?? null;
        $price = is_numeric($price) ? (int)$price : null;

        return new self($sku, $name, $price ?? 0);
    }
}
