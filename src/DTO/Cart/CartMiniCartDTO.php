<?php

declare(strict_types=1);

namespace App\Carting\DTO\Cart;

final readonly class CartMiniCartDTO
{
    public function __construct(
        public string $cartToken,
        public string $currencyCode,
        public int $itemCount,
        public int $totalMinor,
        public bool $empty,
        public ?CartSurfaceActionDTO $viewAction,
        public ?CartSurfaceActionDTO $checkoutAction,
    ) {}
}
