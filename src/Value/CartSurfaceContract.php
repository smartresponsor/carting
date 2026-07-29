<?php

declare(strict_types=1);

namespace App\Carting\Value;

use App\Interfacing\Contract\InterfaceSurfaceRenderableInterface;

final readonly class CartSurfaceContract implements InterfaceSurfaceRenderableInterface
{
    public const WORD = 'cart';
    public const VIEW_SUMMARY = 'summary';

    /**
     * @param array<string, string> $slotMap
     * @param list<CartSurfaceActionValue> $actions
     * @param list<CartNavigationItemValue> $navigation
     * @param array<string, mixed> $slots
     */
    public function __construct(
        public string $word,
        public string $view,
        public string $templateName,
        public array $slotMap,
        public string $cartToken,
        public CartSummaryValue $summary,
        public CartMiniCartValue $miniCart,
        public array $actions,
        public array $navigation,
        public array $slots,
    ) {}

    /** @return array{word: string, view: string, templateName: string, slotMap: array<string, string>, cartToken: string, slots: array<string, mixed>} */
    public function toTemplateContext(): array
    {
        return [
            'word' => $this->word,
            'view' => $this->view,
            'templateName' => $this->templateName,
            'slotMap' => $this->slotMap,
            'cartToken' => $this->cartToken,
            'slots' => $this->slots,
        ];
    }

    /** @return array{word: string, view: string, cartToken: string, slots: array<string, mixed>} */
    public function toFallbackData(): array
    {
        return [
            'word' => $this->word,
            'view' => $this->view,
            'cartToken' => $this->cartToken,
            'slots' => $this->slots,
        ];
    }

    public function templateName(): string
    {
        return $this->templateName;
    }
}
