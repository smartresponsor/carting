<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Service;

use App\Carting\Provider\CartUnavailableOfferProvider;
use PHPUnit\Framework\TestCase;

final class CartUnavailableOfferProviderTest extends TestCase
{
    public function testFailsClosedWhenExternalOfferResolutionIsNotConfigured(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cart offer resolution is not configured.');

        (new CartUnavailableOfferProvider())->provideOfferSnapshot('offer-1', 1);
    }
}
