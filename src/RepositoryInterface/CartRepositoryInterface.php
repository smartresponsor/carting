<?php

declare(strict_types=1);

namespace App\Carting\RepositoryInterface;

use App\Carting\Entity\Cart;

/**
 * Defines the CartRepositoryInterface responsibility used by the Carting component runtime.
 */
interface CartRepositoryInterface
{
    /**
     * Returns the value produced by findActiveByToken for this Carting runtime responsibility.
     */
    public function findActiveByToken(string $cartToken): ?Cart;

    /**
     * Returns the value produced by findActiveByOwnerReference for this Carting runtime responsibility.
     */
    public function findActiveByOwnerReference(string $ownerReference): ?Cart;

    /**
     * Executes the save behavior owned by this Carting runtime responsibility.
     */
    public function save(Cart $cart): void;
}
