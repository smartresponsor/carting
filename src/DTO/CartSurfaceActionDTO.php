<?php

declare(strict_types=1);

namespace App\Carting\DTO;

/**
 * Defines the CartSurfaceActionDTO responsibility used by the Carting component runtime.
 */
final readonly class CartSurfaceActionDTO
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $method,
        public string $uri,
        public bool $primary = false,
        public bool $destructive = false,
    ) {}
}
