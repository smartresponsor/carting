<?php

declare(strict_types=1);

namespace App\Carting\Tests\Service;

use App\Carting\Service\Cart\CartSurfaceContractFactory;
use App\Carting\Value\CartSummaryValue;
use PHPUnit\Framework\TestCase;

final class CartSurfaceContractFactoryTest extends TestCase
{
    public function testCreatesEmptyCartSurfaceWithoutCheckoutAction(): void
    {
        $summary = new CartSummaryValue('token-empty', 'USD', 0, 0, 0, 0, []);
        $surface = (new CartSurfaceContractFactory())->createSummarySurface($summary);

        self::assertSame('cart', $surface->word);
        self::assertSame('summary', $surface->view);
        self::assertSame('cart/base.html.twig', $surface->templateName);
        self::assertArrayHasKey('summary', $surface->slotMap);
        self::assertTrue($surface->miniCart->empty);
        self::assertTrue($surface->slots['empty']);
        self::assertNull($surface->miniCart->checkoutAction);
        self::assertSame(0, $surface->navigation[0]->badgeCount);
        self::assertSame('cart', $surface->navigation[0]->key);
        self::assertCount(1, $surface->actions);
        self::assertSame('view', $surface->actions[0]->key);
    }

    public function testCreatesPopulatedCartSurfaceWithCheckoutAction(): void
    {
        $summary = new CartSummaryValue('token-full', 'USD', 3, 1500, 0, 1500, []);
        $surface = (new CartSurfaceContractFactory())->createSummarySurface($summary);
        $fallbackData = $surface->toFallbackData();

        self::assertFalse($surface->miniCart->empty);
        self::assertFalse($surface->slots['empty']);
        self::assertNotNull($surface->miniCart->checkoutAction);
        self::assertSame('checkout', $surface->miniCart->checkoutAction->key);
        self::assertSame(3, $surface->navigation[0]->badgeCount);
        self::assertCount(3, $surface->actions);
        self::assertSame('cart', $fallbackData['word']);
        self::assertArrayHasKey('slots', $fallbackData);
    }
}
