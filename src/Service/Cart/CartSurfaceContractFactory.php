<?php

declare(strict_types=1);

namespace App\Carting\Service\Cart;

use App\Carting\DTO\Cart\CartMiniCartDTO;
use App\Carting\DTO\Cart\CartNavigationItemDTO;
use App\Carting\DTO\Cart\CartSummaryDTO;
use App\Carting\DTO\Cart\CartSurfaceActionDTO;
use App\Carting\Contract\Cart\CartSurfaceContract;

final class CartSurfaceContractFactory
{
    public function createSummarySurface(CartSummaryDTO $summary): CartSurfaceContract
    {
        $actions = $this->createActions($summary);
        $miniCart = $this->createMiniCart($summary, $actions);
        $navigation = $this->createNavigation($summary);

        return new CartSurfaceContract(
            CartSurfaceContract::WORD,
            CartSurfaceContract::VIEW_SUMMARY,
            'cart/base.html.twig',
            $this->slotMap(),
            $summary->cartToken,
            $summary,
            $miniCart,
            $actions,
            $navigation,
            $this->createSummarySlots($summary, $miniCart, $actions, $navigation),
        );
    }

    /** @return list<CartSurfaceActionDTO> */
    public function createActions(CartSummaryDTO $summary): array
    {
        $actions = [
            new CartSurfaceActionDTO('view', 'View cart', 'GET', '/cart', true),
        ];

        if ($summary->itemCount > 0) {
            $actions[] = new CartSurfaceActionDTO('checkout', 'Checkout', 'POST', '/cart/checkout', true);
            $actions[] = new CartSurfaceActionDTO('clear', 'Clear cart', 'DELETE', '/cart', false, true);
        }

        return $actions;
    }

    /** @param list<CartSurfaceActionDTO> $actions */
    public function createMiniCart(CartSummaryDTO $summary, array $actions = []): CartMiniCartDTO
    {
        if ([] === $actions) {
            $actions = $this->createActions($summary);
        }

        return new CartMiniCartDTO(
            $summary->cartToken,
            $summary->currencyCode,
            $summary->itemCount,
            $summary->totalMinor,
            0 === $summary->itemCount,
            $this->findAction($actions, 'view'),
            $this->findAction($actions, 'checkout'),
        );
    }

    /** @return list<CartNavigationItemDTO> */
    public function createNavigation(CartSummaryDTO $summary): array
    {
        return [
            new CartNavigationItemDTO('cart', 'Cart', '/cart', $summary->itemCount),
        ];
    }

    /** @return array<string, string> */
    private function slotMap(): array
    {
        return [
            'summary' => 'Summary',
            'miniCart' => 'Mini cart',
            'items' => 'Items',
            'actions' => 'Actions',
            'navigation' => 'Navigation',
            'empty' => 'Empty state',
        ];
    }

    /**
     * @param list<CartSurfaceActionDTO> $actions
     * @param list<CartNavigationItemDTO> $navigation
     *
     * @return array<string, mixed>
     */
    private function createSummarySlots(CartSummaryDTO $summary, CartMiniCartDTO $miniCart, array $actions, array $navigation): array
    {
        return [
            'title' => 'Cart',
            'word' => CartSurfaceContract::WORD,
            'cartToken' => $summary->cartToken,
            'summary' => $summary,
            'miniCart' => $miniCart,
            'items' => $summary->items,
            'actions' => $actions,
            'navigation' => $navigation,
            'empty' => 0 === $summary->itemCount,
        ];
    }

    /** @param list<CartSurfaceActionDTO> $actions */
    private function findAction(array $actions, string $key): ?CartSurfaceActionDTO
    {
        foreach ($actions as $action) {
            if ($action->key === $key) {
                return $action;
            }
        }

        return null;
    }
}
