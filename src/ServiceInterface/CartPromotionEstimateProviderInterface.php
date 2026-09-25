<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface;

use App\Carting\DTO\CartAdjustmentEstimateDTO;
use App\Carting\Entity\CartEntity;

/**
 * Defines the CartPromotionEstimateProviderInterface responsibility used by the Carting component runtime.
 */
interface CartPromotionEstimateProviderInterface
{
    /**
     * Returns typed promotion adjustment facts for the mutable cart.
     *
     * @return list<CartAdjustmentEstimateDTO>
     */
    public function estimatePromotions(CartEntity $cart): array;
}
