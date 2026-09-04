<?php

declare(strict_types=1);

namespace App\Carting\Service\Cart;

use App\Carting\Entity\Cart;
use App\Carting\Entity\CartAdjustmentEntity;
use App\Carting\Enum\CartAdjustmentType;
use App\Carting\ServiceInterface\Cart\CartPriceEstimateProviderInterface;
use App\Carting\ServiceInterface\Cart\CartPromotionEstimateProviderInterface;
use App\Carting\ServiceInterface\Cart\CartTaxEstimateProviderInterface;

final class CartAdjustmentEstimateService
{
    public function __construct(
        private readonly ?CartPriceEstimateProviderInterface $priceEstimateProvider = null,
        private readonly ?CartPromotionEstimateProviderInterface $promotionEstimateProvider = null,
        private readonly ?CartTaxEstimateProviderInterface $taxEstimateProvider = null,
    ) {}

    public function refresh(Cart $cart): void
    {
        if ($this->priceEstimateProvider instanceof CartPriceEstimateProviderInterface) {
            $cart->removeAdjustmentsOfType(CartAdjustmentType::PriceEstimate);
            $estimatedTotalMinor = $this->priceEstimateProvider->estimateTotalMinor($cart);
            if ($estimatedTotalMinor < 0) {
                throw new \UnexpectedValueException('Price estimate provider returned a negative total.');
            }

            $deltaMinor = $estimatedTotalMinor - $this->itemSubtotalMinor($cart);
            if (0 !== $deltaMinor) {
                $cart->addAdjustment(new CartAdjustmentEntity(
                    $cart,
                    CartAdjustmentType::PriceEstimate,
                    'Price estimate',
                    $deltaMinor,
                ));
            }
        }

        if ($this->promotionEstimateProvider instanceof CartPromotionEstimateProviderInterface) {
            $cart->removeAdjustmentsOfType(CartAdjustmentType::Promotion);
            foreach ($this->promotionEstimateProvider->estimatePromotions($cart) as $estimate) {
                $this->addEstimate($cart, CartAdjustmentType::Promotion, $estimate);
            }
        }

        if ($this->taxEstimateProvider instanceof CartTaxEstimateProviderInterface) {
            $cart->removeAdjustmentsOfType(CartAdjustmentType::TaxEstimate);
            foreach ($this->taxEstimateProvider->estimateTaxes($cart) as $estimate) {
                $this->addEstimate($cart, CartAdjustmentType::TaxEstimate, $estimate);
            }
        }
    }

    /** @param array{label:string,amountMinor:int} $estimate */
    private function addEstimate(Cart $cart, CartAdjustmentType $type, array $estimate): void
    {
        $label = trim($estimate['label']);
        if ('' === $label) {
            throw new \UnexpectedValueException('Cart estimate provider returned an empty adjustment label.');
        }

        if (0 === $estimate['amountMinor']) {
            return;
        }

        $cart->addAdjustment(new CartAdjustmentEntity($cart, $type, $label, $estimate['amountMinor']));
    }

    private function itemSubtotalMinor(Cart $cart): int
    {
        $subtotalMinor = 0;
        foreach ($cart->getItems() as $item) {
            $subtotalMinor += $item->getLineTotalMinor();
        }

        return $subtotalMinor;
    }
}
