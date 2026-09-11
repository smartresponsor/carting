<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface;

use App\Carting\Entity\Cart;

/**
 * Defines the CartTaxEstimateProviderInterface responsibility used by the Carting component runtime.
 */
interface CartTaxEstimateProviderInterface
{
    /**
     * Executes the estimateTaxes behavior owned by this Carting runtime responsibility.
     * @return list<array{label:string,amountMinor:int}>
     */
    public function estimateTaxes(Cart $cart): array;
}
