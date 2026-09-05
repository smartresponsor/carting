<?php

declare(strict_types=1);

namespace App\Carting\DTO\Cart;

final readonly class CartNavigationItemDTO
{
    public function __construct(
        public string $key,
        public string $label,
        public string $uri,
        public ?int $badgeCount = null,
    ) {}
}
