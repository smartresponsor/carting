<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface;

/**
 * Defines the CartAvailabilityCheckerInterface responsibility used by the Carting component runtime.
 */
interface CartAvailabilityCheckerInterface
{
    /**
     * Returns the value produced by isAvailable for this Carting runtime responsibility.
     */
    public function isAvailable(string $offerReference, int $quantity): bool;
}
