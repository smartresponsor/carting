<?php

declare(strict_types=1);

namespace App\Carting\Service;

use App\Carting\Entity\CartEntity;
use App\Carting\DTO\CartAdjustmentViewDTO;
use App\Carting\DTO\CartItemViewDTO;
use App\Carting\DTO\CartSummaryDTO;

/**
 * Defines the CartSummaryService responsibility used by the Carting component runtime.
 */
final class CartSummaryService
{
    /**
     * Returns the value produced by summarize for this Carting runtime responsibility.
     */
    public function summarize(CartEntity $cart): CartSummaryDTO
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
        $adjustments = [];
        foreach ($cart->getAdjustments() as $adjustment) {
            $adjustmentTotalMinor += $adjustment->getAmountMinor();
            $adjustments[] = new CartAdjustmentViewDTO(
                $adjustment->getType(),
                $adjustment->getLabel(),
                $adjustment->getAmountMinor(),
                $adjustment->getSourceReference(),
            );
        }

        return new CartSummaryDTO(
            $cart->getCartToken(),
            $cart->getCurrencyCode(),
            $itemCount,
            $subtotalMinor,
            $adjustmentTotalMinor,
            $subtotalMinor + $adjustmentTotalMinor,
            $items,
            $adjustments,
        );
    }
}
