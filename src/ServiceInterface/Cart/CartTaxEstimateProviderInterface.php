<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface\Cart;

use App\Carting\Entity\Cart;

interface CartTaxEstimateProviderInterface
{
    /** @return list<array{label:string,amountMinor:int}> */
    public function estimateTaxes(Cart $cart): array;
}
