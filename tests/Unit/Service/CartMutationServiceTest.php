<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Service;

use App\Carting\DTO\CartAvailabilityResultDTO;
use App\Carting\Entity\CartEntity;
use App\Carting\Entity\CartItemEntity;
use App\Carting\Enum\CartMutationFailureReason;
use App\Carting\RepositoryInterface\CartRepositoryInterface;
use App\Carting\Service\CartLifecycleGuardService;
use App\Carting\Service\CartMutationService;
use App\Carting\Service\CartSummaryService;
use App\Carting\Service\CartTokenService;
use App\Carting\ServiceInterface\CartAvailabilityCheckerInterface;
use App\Carting\ServiceInterface\CartOfferProviderInterface;
use App\Carting\Snapshot\CartOfferSnapshot;
use PHPUnit\Framework\TestCase;

final class CartMutationServiceTest extends TestCase
{
    public function testAvailabilityChecksResultingQuantity(): void
    {
        $cart = new CartEntity('token', 'USD');
        $cart->addItem(new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 2));

        $availability = new class implements CartAvailabilityCheckerInterface {
            public int $checkedQuantity = 0;

            public function checkAvailability(string $offerReference, int $quantity): CartAvailabilityResultDTO
            {
                $this->checkedQuantity = $quantity;

                return new CartAvailabilityResultDTO(false, 2, 'stocking-test');
            }
        };

        $service = $this->createService(
            new class implements CartOfferProviderInterface {
                public function provideOfferSnapshot(string $offerReference, int $quantity): CartOfferSnapshot
                {
                    return new CartOfferSnapshot($offerReference, 'Offer 1', 1000, 'USD');
                }
            },
            $availability,
        );

        $result = $service->addItem($cart, 'offer-1', 3);

        self::assertFalse($result->changed);
        self::assertSame(5, $availability->checkedQuantity);
        self::assertSame(2, $cart->getItems()->first()->getQuantity());
    }

    public function testUpdateQuantityRejectsUnavailableRequestedQuantityWithoutPersistence(): void
    {
        $cart = new CartEntity('token', 'USD');
        $cart->addItem(new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 2));

        $availability = new class implements CartAvailabilityCheckerInterface {
            public int $checkedQuantity = 0;

            public function checkAvailability(string $offerReference, int $quantity): CartAvailabilityResultDTO
            {
                $this->checkedQuantity = $quantity;

                return new CartAvailabilityResultDTO(false, 2, 'stocking-test');
            }
        };

        $repository = $this->createMock(CartRepositoryInterface::class);
        $repository->expects(self::never())->method('save');

        $service = $this->createService(
            new class implements CartOfferProviderInterface {
                public function provideOfferSnapshot(string $offerReference, int $quantity): CartOfferSnapshot
                {
                    return new CartOfferSnapshot($offerReference, 'Offer 1', 1000, 'USD');
                }
            },
            $availability,
            $repository,
        );

        $result = $service->updateItemQuantity($cart, 0, 5);

        self::assertFalse($result->changed);
        self::assertSame(CartMutationFailureReason::Unavailable, $result->failureReason);
        self::assertSame(5, $availability->checkedQuantity);
        self::assertSame(2, $cart->getItems()->first()->getQuantity());
    }

    public function testRejectsSnapshotForDifferentOffer(): void
    {
        $service = $this->createService(new class implements CartOfferProviderInterface {
            public function provideOfferSnapshot(string $offerReference, int $quantity): CartOfferSnapshot
            {
                return new CartOfferSnapshot('different-offer', 'Offer', 1000, 'USD');
            }
        });

        $this->expectException(\UnexpectedValueException::class);
        $service->addItem(new CartEntity('token', 'USD'), 'offer-1', 1);
    }

    public function testRejectsSnapshotWithDifferentCurrency(): void
    {
        $service = $this->createService(new class implements CartOfferProviderInterface {
            public function provideOfferSnapshot(string $offerReference, int $quantity): CartOfferSnapshot
            {
                return new CartOfferSnapshot($offerReference, 'Offer', 1000, 'EUR');
            }
        });

        $this->expectException(\UnexpectedValueException::class);
        $service->addItem(new CartEntity('token', 'USD'), 'offer-1', 1);
    }

    public function testRejectsSnapshotWithNegativePrice(): void
    {
        $service = $this->createService(new class implements CartOfferProviderInterface {
            public function provideOfferSnapshot(string $offerReference, int $quantity): CartOfferSnapshot
            {
                return new CartOfferSnapshot($offerReference, 'Offer', -1, 'USD');
            }
        });

        $this->expectException(\UnexpectedValueException::class);
        $service->addItem(new CartEntity('token', 'USD'), 'offer-1', 1);
    }

    private function createService(
        CartOfferProviderInterface $offerProvider,
        ?CartAvailabilityCheckerInterface $availabilityChecker = null,
        ?CartRepositoryInterface $repository = null,
    ): CartMutationService {
        $repository ??= $this->createStub(CartRepositoryInterface::class);

        return new CartMutationService(
            $repository,
            new CartTokenService(),
            new CartSummaryService(),
            new CartLifecycleGuardService(),
            $offerProvider,
            $availabilityChecker,
        );
    }
}
