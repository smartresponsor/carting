<?php

declare(strict_types=1);

namespace App\Carting\DTO;

/**
 * Defines the CartSummaryDTO responsibility used by the Carting component runtime.
 */
final readonly class CartSummaryDTO
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     * @param list<CartItemViewDTO> $items
     */
    public function __construct(
        public string $cartToken,
        public string $currencyCode,
        public int $itemCount,
        public int $subtotalMinor,
        public int $adjustmentTotalMinor,
        public int $totalMinor,
        public array $items,
    ) {}

    /**
     * Returns the value produced by toArray for this Carting runtime responsibility.
     * @return array{cartToken:string,currencyCode:string,itemCount:int,subtotalMinor:int,adjustmentTotalMinor:int,totalMinor:int,items:list<array{id:int,offerReference:string,title:string,quantity:int,unitPriceMinor:int,lineTotalMinor:int}>}
     */
    public function toArray(): array
    {
        return [
            'cartToken' => $this->cartToken,
            'currencyCode' => $this->currencyCode,
            'itemCount' => $this->itemCount,
            'subtotalMinor' => $this->subtotalMinor,
            'adjustmentTotalMinor' => $this->adjustmentTotalMinor,
            'totalMinor' => $this->totalMinor,
            'items' => array_map(static fn(CartItemViewDTO $item): array => $item->toArray(), $this->items),
        ];
    }
}
