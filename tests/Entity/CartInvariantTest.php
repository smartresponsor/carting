<?php

declare(strict_types=1);

namespace App\Carting\Tests\Entity;

use App\Carting\Entity\Cart;
use App\Carting\Entity\CartItem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CartInvariantTest extends TestCase
{
    #[DataProvider('invalidCurrencyProvider')]
    public function testCartRejectsInvalidCurrency(string $currency): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Cart('token', $currency);
    }

    public function testCartItemRejectsNegativePrice(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CartItem('offer', 'Offer', -1, 'USD', 1);
    }

    public function testCartItemNormalizesCurrency(): void
    {
        $item = new CartItem('offer', 'Offer', 100, 'usd', 1);

        self::assertSame('USD', $item->getCurrencyCode());
    }

    /** @return iterable<string, array{string}> */
    public static function invalidCurrencyProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'two letters' => ['US'];
        yield 'numeric' => ['123'];
        yield 'four letters' => ['USDX'];
    }
}
