<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface\Cart;

use App\Carting\Entity\Cart;

interface CartPriceEstimateProviderInterface
{
    public function estimateTotalMinor(Cart $cart): int;
}
