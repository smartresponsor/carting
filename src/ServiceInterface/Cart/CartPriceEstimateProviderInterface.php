<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface\Cart;

use App\Carting\Entity\Cart;

interface CartPriceEstimateProviderInterface
{
    /** @return array{subtotalMinor:int,totalMinor:int,adjustments?:list<array<string,mixed>>} */
    public function estimate(Cart $cart): array;
}
