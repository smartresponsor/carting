<?php

declare(strict_types=1);

namespace App\Carting\DTO;

/**
 * Defines the CartSummaryDTO responsibility used by the Carting component runtime.
 */
final readonly class CartSummaryDTO
{
    /**
     * @param list<CartItemViewDTO> $items
     * @param list<CartAdjustmentViewDTO> $adjustments
     */
    public function __construct(
        public string $cartToken,
        public string $currencyCode,
        public int $itemCount,
        public int $subtotalMinor,
        public int $adjustmentTotalMinor,
        public int $totalMinor,
        public array $items,
        public array $adjustments = [],
    ) {}

    /**
     * @return array{cartToken:string,currencyCode:string,itemCount:int,subtotalMinor:int,adjustmentTotalMinor:int,totalMinor:int,items:list<array{id:int,offerReference:string,title:string,quantity:int,unitPriceMinor:int,lineTotalMinor:int}>,adjustments:list<array{type:string,label:string,amountMinor:int,sourceReference:?string}>}
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
            'adjustments' => array_map(static fn(CartAdjustmentViewDTO $adjustment): array => $adjustment->toArray(), $this->adjustments),
        ];
    }
}
