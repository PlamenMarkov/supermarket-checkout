<?php

namespace App\Tests\Functional;

use App\Entity\Product;
use App\Entity\Promotion;
use App\Enum\PromotionType;
use App\Repository\ProductRepository;
use App\Repository\PromotionRepository;
use App\Services\CheckoutService;
use App\Services\DtoValidatorService;
use App\Services\PricingService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CheckoutFlowTest extends WebTestCase
{
    #[DataProvider('checkoutScenarios')]
    public function testCheckoutTotals(int $expectedTotal, string $skus): void
    {
        $client = static::createClient();
        $this->overrideContainerServices();

        $client->request(
            'POST',
            '/api/checkout/orders',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['skus' => $skus], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('total_cents', $data);
        $this->assertSame($expectedTotal, $data['total_cents'], 'Mismatched total for SKUs: ' . $skus);
    }

    public static function checkoutScenarios(): array
    {
        return [
            [50, 'A'],
            [80, 'AB'],
            [110, 'CDBA'],
            [100, 'AA'],
            [130, 'AAA'],
            [180, 'AAAA'],
            [230, 'AAAAA'],
            [260, 'AAAAAA'],
            [160, 'AAAB'],
            [175, 'AAABB'],
            [185, 'AAABBD'],
            [185, 'DABABA'],
        ];
    }

    private function stubProducts(): ProductRepository
    {
        $productA = (new Product())->setSku('A')->setName('A')->setUnitPriceCents(50);
        $productB = (new Product())->setSku('B')->setName('B')->setUnitPriceCents(30);
        $productC = (new Product())->setSku('C')->setName('C')->setUnitPriceCents(20);
        $productD = (new Product())->setSku('D')->setName('D')->setUnitPriceCents(10);

        $repo = $this->createMock(ProductRepository::class);
        $repo->method('findOneBySku')->willReturnCallback(function (string $sku) use ($productA, $productB, $productC, $productD) {
            return match (strtoupper($sku)) {
                'A' => $productA,
                'B' => $productB,
                'C' => $productC,
                'D' => $productD,
                default => null,
            };
        });

        return $repo;
    }

    private function stubPromotions(): PromotionRepository
    {
        $repo = $this->createMock(PromotionRepository::class);
        $repo->method('findByProduct')->willReturnCallback(function (Product $product) {
            if ($product->getSku() === 'A') {
                $p = (new Promotion())
                    ->setProduct($product)
                    ->setType(PromotionType::N_FOR_PRICE)
                    ->setNQty(3)
                    ->setSpecialPriceCents(130);
                return [$p];
            }
            if ($product->getSku() === 'B') {
                $p = (new Promotion())
                    ->setProduct($product)
                    ->setType(PromotionType::N_FOR_PRICE)
                    ->setNQty(2)
                    ->setSpecialPriceCents(45);
                return [$p];
            }

            return [];
        });

        return $repo;
    }

    private function stubEntityManager(): EntityManagerInterface
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('beginTransaction');
        $em->method('commit');
        $em->method('rollback');
        $em->method('persist');
        $em->method('flush');

        return $em;
    }

    private function overrideContainerServices(): void
    {
        static::getContainer()->set(ProductRepository::class, $this->stubProducts());
        static::getContainer()->set(PromotionRepository::class, $this->stubPromotions());

        $pricing = new PricingService(static::getContainer()->get(PromotionRepository::class));
        static::getContainer()->set(PricingService::class, $pricing);

        $checkout = new CheckoutService(
            $this->stubEntityManager(),
            static::getContainer()->get(PricingService::class),
            static::getContainer()->get(DtoValidatorService::class),
            static::getContainer()->get(ProductRepository::class),
        );
        static::getContainer()->set(CheckoutService::class, $checkout);
    }
}
