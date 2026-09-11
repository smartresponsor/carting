<?php

declare(strict_types=1);

namespace App\Carting\Service;

use App\Carting\Entity\Cart;
use App\Carting\Enum\CartStatus;

/**
 * Defines the CartLifecycleGuardService responsibility used by the Carting component runtime.
 */
final class CartLifecycleGuardService
{
    /**
     * Executes the assertActive behavior owned by this Carting runtime responsibility.
     */
    public function assertActive(Cart $cart, string $operation): void
    {
        if (CartStatus::Active === $cart->getStatus()) {
            return;
        }

        throw new \LogicException(sprintf(
            'Cannot %s cart "%s" because cart status is "%s".',
            $operation,
            $cart->getCartToken(),
            $cart->getStatus()->value,
        ));
    }

    /**
     * Returns the value produced by isExpiredByTime for this Carting runtime responsibility.
     */
    public function isExpiredByTime(Cart $cart, ?\DateTimeImmutable $now = null): bool
    {
        $expiresAt = $cart->getExpiresAt();

        if (!$expiresAt instanceof \DateTimeImmutable) {
            return false;
        }

        return $expiresAt <= ($now ?? new \DateTimeImmutable());
    }
}
