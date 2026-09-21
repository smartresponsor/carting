<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface;

use App\Carting\DTO\CartAvailabilityResultDTO;

/**
 * Defines the CartAvailabilityCheckerInterface responsibility used by the Carting component runtime.
 */
interface CartAvailabilityCheckerInterface
{
    /**
     * Returns a typed inventory availability fact for the requested quantity.
     */
    public function checkAvailability(string $offerReference, int $quantity): CartAvailabilityResultDTO;
}
