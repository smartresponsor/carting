<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Service;

use App\Carting\DTO\CartAdjustmentEstimateDTO;
use App\Carting\DTO\CartPriceEstimateDTO;
use App\Carting\Entity\Cart;
use App\Carting\Entity\CartAdjustmentEntity;
use App\Carting\Entity\CartItem;
use App\Carting\Enum\CartAdjustmentType;
use App\Carting\Service\CartAdjustmentEstimateService;
use App\Carting\Service\CartSummaryService;
use App\Carting\ServiceInterface\CartPriceEstimateProviderInterface;
use App\Carting\ServiceInterface\CartPromotionEstimateProviderInterface;
use App\Carting\ServiceInterface\CartTaxEstimateProviderInterface;
use PHPUnit\Framework\TestCase;

final class CartAdjustmentEstimateServiceTest extends TestCase
{
    public function testRefreshMaterializesEstimatesIdempotentlyAndPreservesManualAdjustments(): void
    {
        $cart = new Cart('token', 'USD');
        $cart->addItem(new CartItem('offer-1', 'Offer 1', 1000, 'USD', 2));
        $cart->addAdjustment(new CartAdjustmentEntity($cart, CartAdjustmentType::Manual, 'Manual fee', 100));
        $cart->addAdjustment(new CartAdjustmentEntity($cart, CartAdjustmentType::Promotion, 'Stale promotion', -50));

        $service = new CartAdjustmentEstimateService(
            new class implements CartPriceEstimateProviderInterface {
                public function estimate(Cart $cart): CartPriceEstimateDTO
                {
                    return new CartPriceEstimateDTO(1800, 'pricing-test');
                }
            },
            new class implements CartPromotionEstimateProviderInterface {
                /** @return list<CartAdjustmentEstimateDTO> */
                public function estimatePromotions(Cart $cart): array
                {
                    return [new CartAdjustmentEstimateDTO('Promotion', -200, 'promoting-test')];
                }
            },
            new class implements CartTaxEstimateProviderInterface {
                /** @return list<CartAdjustmentEstimateDTO> */
                public function estimateTaxes(Cart $cart): array
                {
                    return [new CartAdjustmentEstimateDTO('Tax estimate', 144, 'taxating-test')];
                }
            },
        );

        $service->refresh($cart);
        $service->refresh($cart);
        $summary = (new CartSummaryService())->summarize($cart);

        self::assertCount(4, $cart->getAdjustments());
        self::assertSame(-156, $summary->adjustmentTotalMinor);
        self::assertSame(1844, $summary->totalMinor);
        self::assertSame(
            ['pricing-test', 'promoting-test', 'taxating-test'],
            array_values(array_filter(array_map(
                static fn(CartAdjustmentEntity $adjustment): ?string => $adjustment->getSourceReference(),
                $cart->getAdjustments()->toArray(),
            ))),
        );
    }

    public function testRefreshRejectsNegativePriceEstimate(): void
    {
        $cart = new Cart('token', 'USD');
        $cart->addItem(new CartItem('offer-1', 'Offer 1', 1000, 'USD', 1));
        $service = new CartAdjustmentEstimateService(new class implements CartPriceEstimateProviderInterface {
            public function estimate(Cart $cart): CartPriceEstimateDTO
            {
                return new CartPriceEstimateDTO(-1);
            }
        });

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must not be negative');

        $service->refresh($cart);
    }
}
