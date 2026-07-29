<?php

declare(strict_types=1);

namespace App\Carting\Value;

final readonly class CartItemViewValue
{
    public function __construct(
        public int $id,
        public string $offerReference,
        public string $title,
        public int $quantity,
        public int $unitPriceMinor,
        public int $lineTotalMinor,
    ) {}
}
