<?php

declare(strict_types=1);

namespace App\Carting\DTO;

/**
 * Defines the CartMiniCartDTO responsibility used by the Carting component runtime.
 */
final readonly class CartMiniCartDTO
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
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
