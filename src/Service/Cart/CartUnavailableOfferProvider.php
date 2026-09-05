<?php

declare(strict_types=1);

namespace App\Carting\Service\Cart;

use App\Carting\ServiceInterface\Cart\CartOfferProviderInterface;
use App\Carting\Snapshot\Cart\CartOfferSnapshot;

final class CartUnavailableOfferProvider implements CartOfferProviderInterface
{
    public function provideOfferSnapshot(string $offerReference, int $quantity): CartOfferSnapshot
    {
        throw new \LogicException(
            'Cart offer resolution is not configured. Bind CartOfferProviderInterface to a real external offer provider.',
        );
    }
}
