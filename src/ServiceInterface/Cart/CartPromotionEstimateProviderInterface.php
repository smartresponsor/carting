<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface\Cart;

use App\Carting\Entity\Cart;

interface CartPromotionEstimateProviderInterface
{
    /** @return list<array{label:string,amountMinor:int}> */
    public function estimatePromotions(Cart $cart): array;
}
