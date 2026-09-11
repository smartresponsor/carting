<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Entity;

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

    public function testCartCannotConvertBeforeCheckoutPending(): void
    {
        $this->expectException(\LogicException::class);
        (new Cart('token', 'USD'))->markConverted();
    }

    public function testCartCannotMutateAfterCheckoutPending(): void
    {
        $cart = new Cart('token', 'USD');
        $item = new CartItem('offer', 'Offer', 100, 'USD', 1);
        $cart->addItem($item);
        $cart->markCheckoutPending();

        $this->expectException(\LogicException::class);
        $item->changeQuantity(2);
    }

    public function testCartHasDistinctMergedAndAbandonedTerminalStates(): void
    {
        $merged = new Cart('merged', 'USD');
        $merged->markMerged();
        self::assertSame('merged', $merged->getStatus()->value);

        $abandoned = new Cart('abandoned', 'USD');
        $abandoned->markAbandoned();
        self::assertSame('abandoned', $abandoned->getStatus()->value);
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
