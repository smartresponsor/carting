<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface;

use App\Carting\Entity\Cart;

/**
 * Defines the CartPriceEstimateProviderInterface responsibility used by the Carting component runtime.
 */
interface CartPriceEstimateProviderInterface
{
    /**
     * Executes the estimateTotalMinor behavior owned by this Carting runtime responsibility.
     */
    public function estimateTotalMinor(Cart $cart): int;
}
