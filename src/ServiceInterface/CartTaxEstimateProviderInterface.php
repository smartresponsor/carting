<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface;

use App\Carting\DTO\CartAdjustmentEstimateDTO;
use App\Carting\Entity\Cart;

/**
 * Defines the CartTaxEstimateProviderInterface responsibility used by the Carting component runtime.
 */
interface CartTaxEstimateProviderInterface
{
    /**
     * Returns typed tax adjustment facts for the mutable cart.
     *
     * @return list<CartAdjustmentEstimateDTO>
     */
    public function estimateTaxes(Cart $cart): array;
}
