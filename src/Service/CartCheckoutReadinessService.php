<?php

declare(strict_types=1);

namespace App\Carting\Service;

use App\Carting\Entity\CartEntity;
use App\Carting\Enum\CartStatus;
use App\Carting\ServiceInterface\CartAvailabilityCheckerInterface;
use App\Carting\DTO\CartCheckoutReadinessDTO;

/**
 * Defines the CartCheckoutReadinessService responsibility used by the Carting component runtime.
 */
final class CartCheckoutReadinessService
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(
        private readonly CartLifecycleGuardService $lifecycleGuard,
        private readonly ?CartAvailabilityCheckerInterface $availabilityChecker = null,
    ) {}

    /**
     * Executes the inspect behavior owned by this Carting runtime responsibility.
     */
    public function inspect(CartEntity $cart): CartCheckoutReadinessDTO
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

            if ($this->availabilityChecker && !$this->availabilityChecker->checkAvailability($item->getOfferReference(), $item->getQuantity())->available) {
                $messages[] = sprintf('Cart item "%s" is not available in the requested quantity.', $item->getOfferReference());
            }
        }

        if ([] !== $messages) {
            return CartCheckoutReadinessDTO::blocked($messages);
        }

        return CartCheckoutReadinessDTO::ready();
    }
}
