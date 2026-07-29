<?php

declare(strict_types=1);

namespace App\Carting\Value;

final readonly class CartSummaryValue
{
    /** @param list<CartItemViewValue> $items */
    public function __construct(
        public string $cartToken,
        public string $currencyCode,
        public int $itemCount,
        public int $subtotalMinor,
        public int $adjustmentTotalMinor,
        public int $totalMinor,
        public array $items,
    ) {}
}
