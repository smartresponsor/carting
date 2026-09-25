<?php

declare(strict_types=1);

namespace App\Carting\RepositoryInterface;

use App\Carting\Entity\CartEntity;
use App\Carting\Entity\CartCheckoutHandoffEntity;

/**
 * Owns persistence for checkout handoff preparation and acceptance.
 */
interface CartCheckoutHandoffRepositoryInterface
{
    public function findForCart(CartEntity $cart): ?CartCheckoutHandoffEntity;

    public function savePrepared(CartCheckoutHandoffEntity $handoff): void;

    public function saveAccepted(CartCheckoutHandoffEntity $handoff): void;
}
