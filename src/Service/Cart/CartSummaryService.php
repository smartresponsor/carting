<?php

declare(strict_types=1);

namespace App\Carting\Service\Cart;

use App\Carting\Entity\Cart;
use App\Carting\Value\CartItemViewValue;
use App\Carting\Value\CartSummaryValue;

final class CartSummaryService
{
    public function summarize(Cart $cart): CartSummaryValue
    {
        $items = [];
        $itemCount = 0;
        $subtotalMinor = 0;

        foreach ($cart->getItems() as $item) {
            $lineTotal = $item->getLineTotalMinor();
            $subtotalMinor += $lineTotal;
            $itemCount += $item->getQuantity();
            $items[] = new CartItemViewValue(
                (int) $item->getId(),
                $item->getOfferReference(),
                $item->getTitleSnapshot(),
                $item->getQuantity(),
                $item->getUnitPriceMinor(),
                $lineTotal,
            );
        }

        $adjustmentTotalMinor = 0;

        return new CartSummaryValue(
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
