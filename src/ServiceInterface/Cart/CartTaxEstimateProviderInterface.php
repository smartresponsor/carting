<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface\Cart;

use App\Carting\Entity\Cart;

interface CartTaxEstimateProviderInterface
{
    /** @return list<array{label:string,amountMinor:int,metadata?:array<string,mixed>}> */
    public function estimateTaxes(Cart $cart): array;
}
