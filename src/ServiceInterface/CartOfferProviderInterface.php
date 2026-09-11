<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface;

use App\Carting\Snapshot\CartOfferSnapshot;

/**
 * Defines the CartOfferProviderInterface responsibility used by the Carting component runtime.
 */
interface CartOfferProviderInterface
{
    /**
     * Executes the provideOfferSnapshot behavior owned by this Carting runtime responsibility.
     */
    public function provideOfferSnapshot(string $offerReference, int $quantity): CartOfferSnapshot;
}
