<?php

namespace App\Tests\Unit\Value;

use App\Value\Sku;
use PHPUnit\Framework\TestCase;

class SkuTest extends TestCase
{
    public function testFromStringValidSingleLetter(): void
    {
        $sku = Sku::fromString('a');
        $this->assertSame('A', $sku->toString());
        $this->assertSame('A', $sku->toString());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidSkus')]
    public function testFromStringInvalidThrows(string $raw): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Sku::fromString($raw);
    }

    public static function invalidSkus(): array
    {
        return [
            [''],
            [' '],
            ['AA'],
            ['1'],
            ['*'],
            ['ab'],
            ['A1'],
        ];
    }
}
