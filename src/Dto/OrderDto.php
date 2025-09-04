<?php

namespace App\Dto;

use App\Value\Sku;
use Symfony\Component\Validator\Constraints as Assert;

readonly final class OrderDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'SKUs input is required')]
        #[Assert\Length(min: 1, max: 50)]
        #[Assert\Regex(pattern: Sku::CHECKOUT_SKU_VALIDATION_REGEX, message: Sku::CHECKOUT_SKU_VALIDATION_MESSAGE)]
        #[Assert\Type('string')]
        public string $skus,
    ) {}

    public static function fromArray(array $data): self
    {
        $skus = trim((string)($data['skus'] ?? ''));

        return new self($skus);
    }

    public function countSkus(): array
    {
        $counts = [];
        foreach (str_split(strtoupper(trim($this->skus))) as $ch) {
            try {
                $sku = Sku::fromString($ch)->toString();
                $counts[$sku] = ($counts[$sku] ?? 0) + 1;
            } catch (\InvalidArgumentException) {
                // ignore invalid characters
            }
        }

        return $counts;
    }
}
