<?php

declare(strict_types=1);

namespace App\Carting\DTO;

/**
 * Defines the CartNavigationItemDTO responsibility used by the Carting component runtime.
 */
final readonly class CartNavigationItemDTO
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $uri,
        public ?int $badgeCount = null,
    ) {}
}
