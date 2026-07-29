<?php

declare(strict_types=1);

namespace App\Carting\Value;

final readonly class CartOfferSnapshot
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $offerReference,
        public string $title,
        public int $unitPriceMinor,
        public string $currencyCode,
        public array $metadata = [],
    ) {}
}
