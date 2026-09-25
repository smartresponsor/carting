<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Service;

use App\Carting\Entity\CartEntity;
use App\Carting\Entity\CartAdjustmentEntity;
use App\Carting\Entity\CartItemEntity;
use App\Carting\Enum\CartAdjustmentType;
use App\Carting\Service\CartSummaryService;
use PHPUnit\Framework\TestCase;

final class CartSummaryServiceTest extends TestCase
{
    public function testSummarizeCart(): void
    {
        $cart = new CartEntity('token', 'USD');
        $cart->addItem(new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 2));

        $summary = (new CartSummaryService())->summarize($cart);

        self::assertSame(2, $summary->itemCount);
        self::assertSame(2000, $summary->subtotalMinor);
        self::assertSame(2000, $summary->totalMinor);
    }

    public function testSummarizeCartIncludesPersistedAdjustments(): void
    {
        $cart = new CartEntity('token', 'USD');
        $cart->addItem(new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 2));
        $cart->addAdjustment(new CartAdjustmentEntity($cart, CartAdjustmentType::Promotion, 'Promotion', -500, 'promotion-1'));
        $cart->addAdjustment(new CartAdjustmentEntity($cart, CartAdjustmentType::TaxEstimate, 'Tax estimate', 160, 'tax-1'));

        $summary = (new CartSummaryService())->summarize($cart);

        self::assertSame(-340, $summary->adjustmentTotalMinor);
        self::assertSame(1660, $summary->totalMinor);
        self::assertSame('promotion-1', $summary->adjustments[0]->sourceReference);
        self::assertSame('tax-1', $summary->adjustments[1]->sourceReference);
    }
}
