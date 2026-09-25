<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Service;

use App\Carting\Entity\CartEntity;
use App\Carting\Entity\CartItemEntity;
use App\Carting\Service\CartCheckoutReadinessService;
use App\Carting\Service\CartLifecycleGuardService;
use PHPUnit\Framework\TestCase;

final class CartCheckoutReadinessServiceTest extends TestCase
{
    public function testEmptyCartIsNotReadyForCheckout(): void
    {
        $cart = new CartEntity('token', 'USD');
        $readiness = $this->createService()->inspect($cart);

        self::assertFalse($readiness->ready);
        self::assertContains('Cart has no items.', $readiness->messages);
    }

    public function testActiveCartWithItemsIsReadyForCheckout(): void
    {
        $cart = new CartEntity('token', 'USD');
        $cart->addItem(new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 1));

        $readiness = $this->createService()->inspect($cart);

        self::assertTrue($readiness->ready);
        self::assertSame([], $readiness->messages);
    }

    public function testConvertedCartIsNotReadyForCheckoutAgain(): void
    {
        $cart = new CartEntity('token', 'USD');
        $cart->addItem(new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 1));
        $cart->markCheckoutPending();
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
