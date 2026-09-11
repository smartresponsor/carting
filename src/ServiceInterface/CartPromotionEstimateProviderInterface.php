<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface;

use App\Carting\Entity\Cart;

/**
 * Defines the CartPromotionEstimateProviderInterface responsibility used by the Carting component runtime.
 */
interface CartPromotionEstimateProviderInterface
{
    /**
     * Executes the estimatePromotions behavior owned by this Carting runtime responsibility.
     * @return list<array{label:string,amountMinor:int}>
     */
    public function estimatePromotions(Cart $cart): array;
}
