<?php

declare(strict_types=1);

namespace App\Carting\Provider;

use App\Carting\ServiceInterface\CartOfferProviderInterface;
use App\Carting\Snapshot\CartOfferSnapshot;

/**
 * Defines the CartUnavailableOfferProvider responsibility used by the Carting component runtime.
 */
final class CartUnavailableOfferProvider implements CartOfferProviderInterface
{
    /**
     * Executes the provideOfferSnapshot behavior owned by this Carting runtime responsibility.
     */
    public function provideOfferSnapshot(string $offerReference, int $quantity): CartOfferSnapshot
    {
        throw new \LogicException(
            'Cart offer resolution is not configured. Bind CartOfferProviderInterface to a real external offer provider.',
        );
    }
}
