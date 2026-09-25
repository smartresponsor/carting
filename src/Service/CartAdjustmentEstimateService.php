<?php

declare(strict_types=1);

namespace App\Carting\Service;

use App\Carting\DTO\CartAdjustmentEstimateDTO;
use App\Carting\Entity\CartEntity;
use App\Carting\Entity\CartAdjustmentEntity;
use App\Carting\Enum\CartAdjustmentType;
use App\Carting\ServiceInterface\CartPriceEstimateProviderInterface;
use App\Carting\ServiceInterface\CartPromotionEstimateProviderInterface;
use App\Carting\ServiceInterface\CartTaxEstimateProviderInterface;

/**
 * Defines the CartAdjustmentEstimateService responsibility used by the Carting component runtime.
 */
final class CartAdjustmentEstimateService
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(
        private readonly ?CartPriceEstimateProviderInterface $priceEstimateProvider = null,
        private readonly ?CartPromotionEstimateProviderInterface $promotionEstimateProvider = null,
        private readonly ?CartTaxEstimateProviderInterface $taxEstimateProvider = null,
    ) {}

    /**
     * Executes the refresh behavior owned by this Carting runtime responsibility.
     */
    public function refresh(CartEntity $cart): void
    {
        if ($this->priceEstimateProvider instanceof CartPriceEstimateProviderInterface) {
            $cart->removeAdjustmentsOfType(CartAdjustmentType::PriceEstimate);
            $estimate = $this->priceEstimateProvider->estimate($cart);
            $deltaMinor = $estimate->totalMinor - $this->itemSubtotalMinor($cart);
            if (0 !== $deltaMinor) {
                $cart->addAdjustment(new CartAdjustmentEntity(
                    $cart,
                    CartAdjustmentType::PriceEstimate,
                    'Price estimate',
                    $deltaMinor,
                    $estimate->sourceReference,
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

    /**
     * Materializes one typed external adjustment fact into Carting persistence.
     */
    private function addEstimate(CartEntity $cart, CartAdjustmentType $type, CartAdjustmentEstimateDTO $estimate): void
    {
        if (0 === $estimate->amountMinor) {
            return;
        }

        $cart->addAdjustment(new CartAdjustmentEntity($cart, $type, $estimate->label, $estimate->amountMinor, $estimate->sourceReference));
    }

    /**
     * Executes the itemSubtotalMinor behavior owned by this Carting runtime responsibility.
     */
    private function itemSubtotalMinor(CartEntity $cart): int
    {
        $subtotalMinor = 0;
        foreach ($cart->getItems() as $item) {
            $subtotalMinor += $item->getLineTotalMinor();
        }

        return $subtotalMinor;
    }
}
