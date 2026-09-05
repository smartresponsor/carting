<?php

declare(strict_types=1);

namespace App\Carting\Service\Cart;

use App\Carting\Entity\Cart;
use App\Carting\DTO\Cart\CartItemViewDTO;
use App\Carting\DTO\Cart\CartSummaryDTO;

final class CartSummaryService
{
    public function summarize(Cart $cart): CartSummaryDTO
    {
        $items = [];
        $itemCount = 0;
        $subtotalMinor = 0;

        foreach ($cart->getItems() as $item) {
            $lineTotal = $item->getLineTotalMinor();
            $subtotalMinor += $lineTotal;
            $itemCount += $item->getQuantity();
            $items[] = new CartItemViewDTO(
                (int) $item->getId(),
                $item->getOfferReference(),
                $item->getTitleSnapshot(),
                $item->getQuantity(),
                $item->getUnitPriceMinor(),
                $lineTotal,
            );
        }

        $adjustmentTotalMinor = 0;
        foreach ($cart->getAdjustments() as $adjustment) {
            $adjustmentTotalMinor += $adjustment->getAmountMinor();
        }

        return new CartSummaryDTO(
            $cart->getCartToken(),
            $cart->getCurrencyCode(),
            $itemCount,
            $subtotalMinor,
            $adjustmentTotalMinor,
            $subtotalMinor + $adjustmentTotalMinor,
            $items,
        );
    }
}
