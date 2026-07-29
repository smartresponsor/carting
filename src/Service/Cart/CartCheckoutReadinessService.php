<?php

declare(strict_types=1);

namespace App\Carting\Service\Cart;

use App\Carting\Entity\Cart;
use App\Carting\Enum\CartStatus;
use App\Carting\ServiceInterface\Cart\CartAvailabilityCheckerInterface;
use App\Carting\Value\CartCheckoutReadinessValue;

final class CartCheckoutReadinessService
{
    public function __construct(
        private readonly CartLifecycleGuardService $lifecycleGuard,
        private readonly ?CartAvailabilityCheckerInterface $availabilityChecker = null,
    ) {}

    public function inspect(Cart $cart): CartCheckoutReadinessValue
    {
        $messages = [];

        if (CartStatus::Active !== $cart->getStatus()) {
            $messages[] = sprintf('Cart is not active. Current status: %s.', $cart->getStatus()->value);
        }

        if ($this->lifecycleGuard->isExpiredByTime($cart)) {
            $messages[] = 'Cart is expired by time.';
        }

        if ($cart->getItems()->isEmpty()) {
            $messages[] = 'Cart has no items.';
        }

        foreach ($cart->getItems() as $item) {
            if ($item->getCurrencyCode() !== $cart->getCurrencyCode()) {
                $messages[] = sprintf('Cart item "%s" currency does not match cart currency.', $item->getOfferReference());
            }

            if ($this->availabilityChecker && !$this->availabilityChecker->isAvailable($item->getOfferReference(), $item->getQuantity())) {
                $messages[] = sprintf('Cart item "%s" is not available in the requested quantity.', $item->getOfferReference());
            }
        }

        if ([] !== $messages) {
            return CartCheckoutReadinessValue::blocked($messages);
        }

        return CartCheckoutReadinessValue::ready();
    }
}
