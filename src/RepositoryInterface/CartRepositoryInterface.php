<?php

declare(strict_types=1);

namespace App\Carting\RepositoryInterface;

use App\Carting\Entity\Cart;

interface CartRepositoryInterface
{
    public function findActiveByToken(string $cartToken): ?Cart;

    public function findActiveByOwnerReference(string $ownerReference): ?Cart;

    public function save(Cart $cart): void;
}
