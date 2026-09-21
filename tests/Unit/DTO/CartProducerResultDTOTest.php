<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\DTO;

use App\Carting\DTO\CartAdjustmentEstimateDTO;
use App\Carting\DTO\CartAdjustmentViewDTO;
use App\Carting\DTO\CartAvailabilityResultDTO;
use App\Carting\DTO\CartCheckoutPayloadDTO;
use App\Carting\DTO\CartPriceEstimateDTO;
use App\Carting\Enum\CartAdjustmentType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CartProducerResultDTOTest extends TestCase
{
    public function testAdjustmentViewSerializesProducerProvenance(): void
    {
        $view = new CartAdjustmentViewDTO(CartAdjustmentType::Promotion, 'Promotion', -250, 'promotion-42');

        self::assertSame([
            'type' => 'promotion',
            'label' => 'Promotion',
            'amountMinor' => -250,
            'sourceReference' => 'promotion-42',
        ], $view->toArray());
    }

    public function testCheckoutPayloadReadsHistoricalPayloadWithoutAdjustments(): void
    {
        $payload = CartCheckoutPayloadDTO::fromArray([
            'cartToken' => 'cart-1',
            'ownerReference' => null,
            'currencyCode' => 'USD',
            'subtotalMinor' => 1000,
            'totalMinor' => 1000,
            'lines' => [],
        ]);

        self::assertSame([], $payload->adjustments);
        self::assertSame([], $payload->toArray()['adjustments']);
    }

    public function testAvailabilityRejectsNegativeAvailableQuantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CartAvailabilityResultDTO(true, -1);
    }

    #[DataProvider('invalidSourceReferenceProvider')]
    public function testProducerFactsRejectBlankSourceReference(string $kind): void
    {
        $this->expectException(\InvalidArgumentException::class);

        match ($kind) {
            'adjustment' => new CartAdjustmentEstimateDTO('Promotion', -100, '   '),
            'price' => new CartPriceEstimateDTO(1000, '   '),
            'availability' => new CartAvailabilityResultDTO(true, 1, '   '),
            default => throw new \LogicException(sprintf('Unsupported producer fact kind "%s".', $kind)),
        };
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidSourceReferenceProvider(): iterable
    {
        yield 'adjustment' => ['adjustment'];
        yield 'price' => ['price'];
        yield 'availability' => ['availability'];
    }
}
