<?php

declare(strict_types=1);

namespace App\Carting\Service;

use App\Carting\Entity\CartEntity;
use App\Carting\RepositoryInterface\CartRepositoryInterface;

/**
 * Recovers the currently active persisted cart associated with an authenticated owner.
 */
final class CartRecoveryService
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
    ) {}

    /**
     * Returns the active saved cart for an owner without reactivating terminal carts.
     */
    public function recoverActiveOwnerCart(string $ownerReference): ?CartEntity
    {
        $ownerReference = trim($ownerReference);
        if ('' === $ownerReference) {
            throw new \InvalidArgumentException('Cart owner reference must not be empty.');
        }

        return $this->cartRepository->findActiveByOwnerReference($ownerReference);
    }
}
