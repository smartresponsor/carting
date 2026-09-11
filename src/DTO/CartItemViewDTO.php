<?php

declare(strict_types=1);

namespace App\Carting\DTO;

/**
 * Defines the CartItemViewDTO responsibility used by the Carting component runtime.
 */
final readonly class CartItemViewDTO
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(
        public int $id,
        public string $offerReference,
        public string $title,
        public int $quantity,
        public int $unitPriceMinor,
        public int $lineTotalMinor,
    ) {}

    /**
     * Returns the value produced by toArray for this Carting runtime responsibility.
     * @return array{id:int,offerReference:string,title:string,quantity:int,unitPriceMinor:int,lineTotalMinor:int}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'offerReference' => $this->offerReference,
            'title' => $this->title,
            'quantity' => $this->quantity,
            'unitPriceMinor' => $this->unitPriceMinor,
            'lineTotalMinor' => $this->lineTotalMinor,
        ];
    }
}
