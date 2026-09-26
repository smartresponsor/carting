<?php

declare(strict_types=1);

namespace App\Carting\DTO;

use App\Carting\Enum\CartMutationFailureReason;

/**
 * Defines the CartMutationResultDTO responsibility used by the Carting component runtime.
 */
final readonly class CartMutationResultDTO
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(
        public bool $changed,
        public string $message,
        public CartSummaryDTO $summary,
        public ?CartMutationFailureReason $failureReason = null,
    ) {}

    /**
     * Returns the value produced by toArray for this Carting runtime responsibility.
     * @return array{changed:bool,message:string,summary:array<string,mixed>}
     */
    public function toArray(): array
    {
        return [
            'changed' => $this->changed,
            'message' => $this->message,
            'summary' => $this->summary->toArray(),
        ];
    }
}
