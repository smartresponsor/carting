<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface\Cart;

interface CartAvailabilityCheckerInterface
{
    public function isAvailable(string $offerReference, int $quantity): bool;
}
