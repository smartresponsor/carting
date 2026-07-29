<?php

declare(strict_types=1);

namespace App\Carting\Value;

final readonly class CartMiniCartValue
{
    public function __construct(
        public string $cartToken,
        public string $currencyCode,
        public int $itemCount,
        public int $totalMinor,
        public bool $empty,
        public ?CartSurfaceActionValue $viewAction,
        public ?CartSurfaceActionValue $checkoutAction,
    ) {}
}
