<?php

declare(strict_types=1);

namespace App\Carting\DTO;

/**
 * Defines the CartCheckoutPayloadDTO responsibility used by the Carting component runtime.
 */
final readonly class CartCheckoutPayloadDTO
{
    /**
     * @param list<array{offerReference:string,title:string,quantity:int,unitPriceMinor:int,lineTotalMinor:int}> $lines
     * @param list<array{type:string,label:string,amountMinor:int,sourceReference:?string}> $adjustments
     */
    public function __construct(
        public string $cartToken,
        public ?string $ownerReference,
        public string $currencyCode,
        public int $subtotalMinor,
        public int $totalMinor,
        public array $lines,
        public array $adjustments = [],
    ) {}

    /**
     * @param array{cartToken:string,ownerReference:?string,currencyCode:string,subtotalMinor:int,totalMinor:int,lines:list<array{offerReference:string,title:string,quantity:int,unitPriceMinor:int,lineTotalMinor:int}>,adjustments?:list<array{type:string,label:string,amountMinor:int,sourceReference:?string}>} $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            $payload['cartToken'],
            $payload['ownerReference'],
            $payload['currencyCode'],
            $payload['subtotalMinor'],
            $payload['totalMinor'],
            $payload['lines'],
            $payload['adjustments'] ?? [],
        );
    }

    /**
     * @return array{cartToken:string,ownerReference:?string,currencyCode:string,subtotalMinor:int,totalMinor:int,lines:list<array{offerReference:string,title:string,quantity:int,unitPriceMinor:int,lineTotalMinor:int}>,adjustments:list<array{type:string,label:string,amountMinor:int,sourceReference:?string}>}
     */
    public function toArray(): array
    {
        return [
            'cartToken' => $this->cartToken,
            'ownerReference' => $this->ownerReference,
            'currencyCode' => $this->currencyCode,
            'subtotalMinor' => $this->subtotalMinor,
            'totalMinor' => $this->totalMinor,
            'lines' => $this->lines,
            'adjustments' => $this->adjustments,
        ];
    }
}
