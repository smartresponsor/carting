<?php

declare(strict_types=1);

namespace App\Carting\Service\Cart;

use App\Carting\Entity\Cart;
use App\Carting\Enum\CartStatus;

final class CartLifecycleGuardService
{
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

    public function isExpiredByTime(Cart $cart, ?\DateTimeImmutable $now = null): bool
    {
        $expiresAt = $cart->getExpiresAt();

        if (!$expiresAt instanceof \DateTimeImmutable) {
            return false;
        }

        return $expiresAt <= ($now ?? new \DateTimeImmutable());
    }
}
