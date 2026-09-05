<?php

declare(strict_types=1);

namespace App\Carting\DTO\Cart;

final readonly class CartItemViewDTO
{
    public function __construct(
        public int $id,
        public string $offerReference,
        public string $title,
        public int $quantity,
        public int $unitPriceMinor,
        public int $lineTotalMinor,
    ) {}

    /** @return array{id:int,offerReference:string,title:string,quantity:int,unitPriceMinor:int,lineTotalMinor:int} */
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
