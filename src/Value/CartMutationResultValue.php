<?php

declare(strict_types=1);

namespace App\Carting\Value;

final readonly class CartMutationResultValue
{
    public function __construct(
        public bool $changed,
        public string $message,
        public CartSummaryValue $summary,
    ) {}
}
