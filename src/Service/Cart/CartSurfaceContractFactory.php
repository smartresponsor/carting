<?php

declare(strict_types=1);

namespace App\Carting\Service\Cart;

use App\Carting\Value\CartMiniCartValue;
use App\Carting\Value\CartNavigationItemValue;
use App\Carting\Value\CartSummaryValue;
use App\Carting\Value\CartSurfaceActionValue;
use App\Carting\Value\CartSurfaceContract;

final class CartSurfaceContractFactory
{
    public function createSummarySurface(CartSummaryValue $summary): CartSurfaceContract
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

    /** @return list<CartSurfaceActionValue> */
    public function createActions(CartSummaryValue $summary): array
    {
        $actions = [
            new CartSurfaceActionValue('view', 'View cart', 'GET', '/cart', true),
        ];

        if ($summary->itemCount > 0) {
            $actions[] = new CartSurfaceActionValue('checkout', 'Checkout', 'POST', '/cart/checkout', true);
            $actions[] = new CartSurfaceActionValue('clear', 'Clear cart', 'DELETE', '/cart', false, true);
        }

        return $actions;
    }

    /** @param list<CartSurfaceActionValue> $actions */
    public function createMiniCart(CartSummaryValue $summary, array $actions = []): CartMiniCartValue
    {
        if ([] === $actions) {
            $actions = $this->createActions($summary);
        }

        return new CartMiniCartValue(
            $summary->cartToken,
            $summary->currencyCode,
            $summary->itemCount,
            $summary->totalMinor,
            0 === $summary->itemCount,
            $this->findAction($actions, 'view'),
            $this->findAction($actions, 'checkout'),
        );
    }

    /** @return list<CartNavigationItemValue> */
    public function createNavigation(CartSummaryValue $summary): array
    {
        return [
            new CartNavigationItemValue('cart', 'Cart', '/cart', $summary->itemCount),
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
     * @param list<CartSurfaceActionValue> $actions
     * @param list<CartNavigationItemValue> $navigation
     *
     * @return array<string, mixed>
     */
    private function createSummarySlots(CartSummaryValue $summary, CartMiniCartValue $miniCart, array $actions, array $navigation): array
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

    /** @param list<CartSurfaceActionValue> $actions */
    private function findAction(array $actions, string $key): ?CartSurfaceActionValue
    {
        foreach ($actions as $action) {
            if ($action->key === $key) {
                return $action;
            }
        }

        return null;
    }
}
