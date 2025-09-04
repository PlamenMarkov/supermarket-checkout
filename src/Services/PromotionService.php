<?php

namespace App\Services;

use App\Dto\PromotionDto;
use App\Entity\Promotion;
use App\Enum\PromotionType;
use App\Repository\ProductRepository;
use App\Repository\PromotionRepository;
use App\Value\Quantity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly final class PromotionService
{
    public function __construct(
        private PromotionRepository $promotions,
        private ProductRepository   $products,
        private DtoValidatorService $dtoValidator,
    ) {}

    public function list(): array
    {
        return $this->promotions->findAll();
    }

    public function get(int $id): Promotion
    {
        $promotion = $this->promotions->find($id);
        if (!$promotion) {
            throw new NotFoundHttpException('Promotion not found');
        }

        return $promotion;
    }

    public function create(PromotionDto $dto): Promotion
    {
        $this->dtoValidator->assertValid($dto);

        $promotion = new Promotion();

        return $this->persistPromotion($promotion, $dto);
    }

    public function update(int $id, PromotionDto $dto): Promotion
    {
        $this->dtoValidator->assertValid($dto);

        $promotion = $this->get($id);

        return $this->persistPromotion($promotion, $dto);
    }

    public function delete(int $id): void
    {
        $promotion = $this->get($id);
        $this->promotions->remove($promotion, true);
    }

    private function persistPromotion(Promotion $promotion, PromotionDto $dto): Promotion
    {
        $product = $this->products->find($dto->productId);
        if (!$product) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid product_id');
        }

        $type = PromotionType::tryFrom($dto->type);
        if (!$type) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid promotion type');
        }

        if ($dto->specialPriceCents < 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'special_price_cents must be >= 0');
        }

        $promotion
            ->setProduct($product)
            ->setType($type)
            ->setNQty(Quantity::of($dto->nQty)->toInt())
            ->setSpecialPriceCents($dto->specialPriceCents)
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->promotions->save($promotion, true);

        return $promotion;
    }
}
