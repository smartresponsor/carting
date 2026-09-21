<?php

declare(strict_types=1);

namespace App\Carting\DTO;

/**
 * Typed total-price fact consumed from a pricing producer.
 */
final readonly class CartPriceEstimateDTO
{
    public function __construct(
        public int $totalMinor,
        public ?string $sourceReference = null,
    ) {
        if ($this->totalMinor < 0) {
            throw new \InvalidArgumentException('Cart price estimate total must not be negative.');
        }

        if (null !== $this->sourceReference && '' === trim($this->sourceReference)) {
            throw new \InvalidArgumentException('Cart price estimate source reference must not be empty when provided.');
        }
    }
}
