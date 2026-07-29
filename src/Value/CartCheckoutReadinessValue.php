<?php

declare(strict_types=1);

namespace App\Carting\Value;

final readonly class CartCheckoutReadinessValue
{
    /** @param list<string> $messages */
    public function __construct(
        public bool $ready,
        public array $messages = [],
    ) {}

    public static function ready(): self
    {
        return new self(true);
    }

    /** @param list<string> $messages */
    public static function blocked(array $messages): self
    {
        return new self(false, $messages);
    }
}
