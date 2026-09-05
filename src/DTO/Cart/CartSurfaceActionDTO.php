<?php

declare(strict_types=1);

namespace App\Carting\DTO\Cart;

final readonly class CartSurfaceActionDTO
{
    public function __construct(
        public string $key,
        public string $label,
        public string $method,
        public string $uri,
        public bool $primary = false,
        public bool $destructive = false,
    ) {}
}
