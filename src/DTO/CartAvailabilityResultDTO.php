<?php

declare(strict_types=1);

namespace App\Carting\DTO;

/**
 * Typed inventory availability fact consumed by Carting.
 */
final readonly class CartAvailabilityResultDTO
{
    public function __construct(
        public bool $available,
        public ?int $availableQuantity = null,
        public ?string $sourceReference = null,
    ) {
        if (null !== $this->availableQuantity && $this->availableQuantity < 0) {
            throw new \InvalidArgumentException('Available quantity must not be negative.');
        }

        if (null !== $this->sourceReference && '' === trim($this->sourceReference)) {
            throw new \InvalidArgumentException('Availability source reference must not be empty when provided.');
        }
    }
}
