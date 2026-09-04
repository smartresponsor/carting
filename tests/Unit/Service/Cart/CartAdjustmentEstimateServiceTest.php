<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Service\Cart;

use App\Carting\Entity\Cart;
use App\Carting\Entity\CartAdjustmentEntity;
use App\Carting\Entity\CartItem;
use App\Carting\Enum\CartAdjustmentType;
use App\Carting\Service\Cart\CartAdjustmentEstimateService;
use App\Carting\Service\Cart\CartSummaryService;
use App\Carting\ServiceInterface\Cart\CartPriceEstimateProviderInterface;
use App\Carting\ServiceInterface\Cart\CartPromotionEstimateProviderInterface;
use App\Carting\ServiceInterface\Cart\CartTaxEstimateProviderInterface;
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
                public function estimateTotalMinor(Cart $cart): int
                {
                    return 1800;
                }
            },
            new class implements CartPromotionEstimateProviderInterface {
                /** @return list<array{label:string,amountMinor:int}> */
                public function estimatePromotions(Cart $cart): array
                {
                    return [['label' => 'Promotion', 'amountMinor' => -200]];
                }
            },
            new class implements CartTaxEstimateProviderInterface {
                /** @return list<array{label:string,amountMinor:int}> */
                public function estimateTaxes(Cart $cart): array
                {
                    return [['label' => 'Tax estimate', 'amountMinor' => 144]];
                }
            },
        );

        $service->refresh($cart);
        $service->refresh($cart);
        $summary = (new CartSummaryService())->summarize($cart);

        self::assertCount(4, $cart->getAdjustments());
        self::assertSame(-156, $summary->adjustmentTotalMinor);
        self::assertSame(1844, $summary->totalMinor);
    }

    public function testRefreshRejectsNegativePriceEstimate(): void
    {
        $cart = new Cart('token', 'USD');
        $cart->addItem(new CartItem('offer-1', 'Offer 1', 1000, 'USD', 1));
        $service = new CartAdjustmentEstimateService(new class implements CartPriceEstimateProviderInterface {
            public function estimateTotalMinor(Cart $cart): int
            {
                return -1;
            }
        });

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('negative total');

        $service->refresh($cart);
    }
}
