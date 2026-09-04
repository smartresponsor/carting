<?php

declare(strict_types=1);

namespace App\Carting\DTO\Cart;

final readonly class CartMutationResultDTO
{
    public function __construct(
        public bool $changed,
        public string $message,
        public CartSummaryDTO $summary,
    ) {}

    /** @return array{changed:bool,message:string,summary:array<string,mixed>} */
    public function toArray(): array
    {
        return [
            'changed' => $this->changed,
            'message' => $this->message,
            'summary' => $this->summary->toArray(),
        ];
    }
}
