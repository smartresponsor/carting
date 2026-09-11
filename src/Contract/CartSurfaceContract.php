<?php

declare(strict_types=1);

namespace App\Carting\Contract;

use App\Carting\DTO\CartMiniCartDTO;
use App\Carting\DTO\CartNavigationItemDTO;
use App\Carting\DTO\CartSummaryDTO;
use App\Carting\DTO\CartSurfaceActionDTO;
use App\Interfacing\Contract\InterfaceSurfaceRenderableInterface;

/**
 * Defines the CartSurfaceContract responsibility used by the Carting component runtime.
 */
final readonly class CartSurfaceContract implements InterfaceSurfaceRenderableInterface
{
    public const WORD = 'cart';
    public const VIEW_SUMMARY = 'summary';

    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     * @param array<string, string> $slotMap
     * @param list<CartSurfaceActionDTO> $actions
     * @param list<CartNavigationItemDTO> $navigation
     * @param array<string, mixed> $slots
     */
    public function __construct(
        public string $word,
        public string $view,
        public string $templateName,
        public array $slotMap,
        public string $cartToken,
        public CartSummaryDTO $summary,
        public CartMiniCartDTO $miniCart,
        public array $actions,
        public array $navigation,
        public array $slots,
    ) {}

    /**
     * Returns the value produced by toTemplateContext for this Carting runtime responsibility.
     * @return array{word: string, view: string, templateName: string, slotMap: array<string, string>, cartToken: string, slots: array<string, mixed>}
     */
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

    /**
     * Returns the value produced by toFallbackData for this Carting runtime responsibility.
     * @return array{word: string, view: string, cartToken: string, slots: array<string, mixed>}
     */
    public function toFallbackData(): array
    {
        return [
            'word' => $this->word,
            'view' => $this->view,
            'cartToken' => $this->cartToken,
            'slots' => $this->slots,
        ];
    }

    /**
     * Executes the templateName behavior owned by this Carting runtime responsibility.
     */
    public function templateName(): string
    {
        return $this->templateName;
    }
}
