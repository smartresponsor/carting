<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface\Cart;

use App\Carting\Value\CartOfferSnapshot;

interface CartOfferProviderInterface
{
    public function provideOfferSnapshot(string $offerReference, int $quantity): CartOfferSnapshot;
}
