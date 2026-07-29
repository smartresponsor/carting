<?php

declare(strict_types=1);

namespace App\Carting\Tests\Service;

use App\Carting\Entity\Cart;
use App\Carting\Entity\CartItem;
use App\Carting\Service\Cart\CartCheckoutReadinessService;
use App\Carting\Service\Cart\CartLifecycleGuardService;
use PHPUnit\Framework\TestCase;

final class CartCheckoutReadinessServiceTest extends TestCase
{
    public function testEmptyCartIsNotReadyForCheckout(): void
    {
        $cart = new Cart('token', 'USD');
        $readiness = $this->createService()->inspect($cart);

        self::assertFalse($readiness->ready);
        self::assertContains('Cart has no items.', $readiness->messages);
    }

    public function testActiveCartWithItemsIsReadyForCheckout(): void
    {
        $cart = new Cart('token', 'USD');
        $cart->addItem(new CartItem('offer-1', 'Offer 1', 1000, 'USD', 1));

        $readiness = $this->createService()->inspect($cart);

        self::assertTrue($readiness->ready);
        self::assertSame([], $readiness->messages);
    }

    public function testConvertedCartIsNotReadyForCheckoutAgain(): void
    {
        $cart = new Cart('token', 'USD');
        $cart->addItem(new CartItem('offer-1', 'Offer 1', 1000, 'USD', 1));
        $cart->markConverted();

        $readiness = $this->createService()->inspect($cart);

        self::assertFalse($readiness->ready);
        self::assertContains('Cart is not active. Current status: converted.', $readiness->messages);
    }

    private function createService(): CartCheckoutReadinessService
    {
        return new CartCheckoutReadinessService(new CartLifecycleGuardService());
    }
}
