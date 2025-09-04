<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PromotionDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public readonly int $productId,

        #[Assert\NotBlank]
        #[Assert\Choice(['n_for_price'])]
        public readonly string $type,

        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\Positive]
        #[Assert\Length(min: 0, max: 50)]
        public readonly int $nQty,

        #[Assert\NotNull]
        #[Assert\Type('integer')]
        #[Assert\GreaterThanOrEqual(0)]
        public readonly int $specialPriceCents,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int)($data['product_id'] ?? 0),
            (string)($data['type'] ?? ''),
            (int)($data['n_qty'] ?? 0),
            (int)($data['special_price_cents'] ?? 0),
        );
    }
}
