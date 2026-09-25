<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Entity;

use App\Carting\Entity\CartEntity;
use App\Carting\Entity\CartItemEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CartInvariantTest extends TestCase
{
    #[DataProvider('invalidCurrencyProvider')]
    public function testCartRejectsInvalidCurrency(string $currency): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CartEntity('token', $currency);
    }

    public function testCartItemRejectsNegativePrice(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CartItemEntity('offer', 'Offer', -1, 'USD', 1);
    }

    public function testCartItemNormalizesCurrency(): void
    {
        $item = new CartItemEntity('offer', 'Offer', 100, 'usd', 1);

        self::assertSame('USD', $item->getCurrencyCode());
    }

    public function testCartCannotConvertBeforeCheckoutPending(): void
    {
        $this->expectException(\LogicException::class);
        (new CartEntity('token', 'USD'))->markConverted();
    }

    public function testCartCannotMutateAfterCheckoutPending(): void
    {
        $cart = new CartEntity('token', 'USD');
        $item = new CartItemEntity('offer', 'Offer', 100, 'USD', 1);
        $cart->addItem($item);
        $cart->markCheckoutPending();

        $this->expectException(\LogicException::class);
        $item->changeQuantity(2);
    }

    public function testCartHasDistinctMergedAndAbandonedTerminalStates(): void
    {
        $merged = new CartEntity('merged', 'USD');
        $merged->markMerged();
        self::assertSame('merged', $merged->getStatus()->value);

        $abandoned = new CartEntity('abandoned', 'USD');
        $abandoned->markAbandoned();
        self::assertSame('abandoned', $abandoned->getStatus()->value);
    }

    public function testCartUsesObjectingAuditLifecycle(): void
    {
        $cart = new CartEntity('audit-cart', 'USD');

        self::assertNull($cart->getModifiedAt());
        $cart->assignOwner('vendor-1');
        self::assertNotNull($cart->getModifiedAt());
    }

    public function testCartUsesObjectingVersionLifecycle(): void
    {
        $cart = new CartEntity('versioned-cart', 'USD');

        self::assertSame(1, $cart->getObjectVersion());
        self::assertNull($cart->getObjectEtag());
        $cart->bumpObjectVersion('cart-etag');
        self::assertSame('cart-etag', $cart->getObjectEtag());
    }

    public function testCartItemUsesObjectingAuditLifecycle(): void
    {
        $cart = new CartEntity('audit-item', 'USD');
        $item = new CartItemEntity('offer', 'Offer', 100, 'USD', 1);
        $cart->addItem($item);

        self::assertNull($item->getModifiedAt());
        $item->changeQuantity(2);
        self::assertNotNull($item->getModifiedAt());
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
