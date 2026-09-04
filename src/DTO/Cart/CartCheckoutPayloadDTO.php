<?php

declare(strict_types=1);

namespace App\Carting\DTO\Cart;

final readonly class CartCheckoutPayloadDTO
{
    /** @param list<array{offerReference:string,title:string,quantity:int,unitPriceMinor:int,lineTotalMinor:int}> $lines */
    public function __construct(
        public string $cartToken,
        public ?string $ownerReference,
        public string $currencyCode,
        public int $subtotalMinor,
        public int $totalMinor,
        public array $lines,
    ) {}

    /** @param array{cartToken:string,ownerReference:?string,currencyCode:string,subtotalMinor:int,totalMinor:int,lines:list<array{offerReference:string,title:string,quantity:int,unitPriceMinor:int,lineTotalMinor:int}>} $payload */
    public static function fromArray(array $payload): self
    {
        return new self(
            $payload['cartToken'],
            $payload['ownerReference'],
            $payload['currencyCode'],
            $payload['subtotalMinor'],
            $payload['totalMinor'],
            $payload['lines'],
        );
    }

    /** @return array{cartToken:string,ownerReference:?string,currencyCode:string,subtotalMinor:int,totalMinor:int,lines:list<array{offerReference:string,title:string,quantity:int,unitPriceMinor:int,lineTotalMinor:int}>} */
    public function toArray(): array
    {
        return [
            'cartToken' => $this->cartToken,
            'ownerReference' => $this->ownerReference,
            'currencyCode' => $this->currencyCode,
            'subtotalMinor' => $this->subtotalMinor,
            'totalMinor' => $this->totalMinor,
            'lines' => $this->lines,
        ];
    }
}
