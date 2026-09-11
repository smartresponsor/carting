<?php

declare(strict_types=1);

namespace App\Carting\DTO;

/**
 * Defines the CartCheckoutReadinessDTO responsibility used by the Carting component runtime.
 */
final readonly class CartCheckoutReadinessDTO
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     * @param list<string> $messages
     */
    public function __construct(
        public bool $ready,
        public array $messages = [],
    ) {}

    /**
     * Executes the ready behavior owned by this Carting runtime responsibility.
     */
    public static function ready(): self
    {
        return new self(true);
    }

    /**
     * Executes the blocked behavior owned by this Carting runtime responsibility.
     * @param list<string> $messages
     */
    public static function blocked(array $messages): self
    {
        return new self(false, $messages);
    }
}
