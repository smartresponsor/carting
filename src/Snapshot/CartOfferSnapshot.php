<?php

declare(strict_types=1);

namespace App\Carting\Snapshot;

/**
 * Defines the CartOfferSnapshot responsibility used by the Carting component runtime.
 */
final readonly class CartOfferSnapshot
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $offerReference,
        public string $title,
        public int $unitPriceMinor,
        public string $currencyCode,
        public array $metadata = [],
    ) {}
}
