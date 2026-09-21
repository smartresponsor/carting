<?php

declare(strict_types=1);

namespace App\Carting\DTO;

/**
 * Typed adjustment fact consumed from a promotion or tax producer.
 */
final readonly class CartAdjustmentEstimateDTO
{
    public function __construct(
        public string $label,
        public int $amountMinor,
        public ?string $sourceReference = null,
    ) {
        if ('' === trim($this->label)) {
            throw new \InvalidArgumentException('Cart adjustment estimate label must not be empty.');
        }

        if (null !== $this->sourceReference && '' === trim($this->sourceReference)) {
            throw new \InvalidArgumentException('Cart adjustment estimate source reference must not be empty when provided.');
        }
    }
}
