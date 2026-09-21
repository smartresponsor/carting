<?php

declare(strict_types=1);

namespace App\Carting\DTO;

use App\Carting\Enum\CartAdjustmentType;

/**
 * Immutable persisted adjustment projection used by summary and checkout handoff.
 */
final readonly class CartAdjustmentViewDTO
{
    public function __construct(
        public CartAdjustmentType $type,
        public string $label,
        public int $amountMinor,
        public ?string $sourceReference,
    ) {}

    /**
     * @return array{type:string,label:string,amountMinor:int,sourceReference:?string}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'label' => $this->label,
            'amountMinor' => $this->amountMinor,
            'sourceReference' => $this->sourceReference,
        ];
    }
}
