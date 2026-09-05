<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Service\Cart;

use App\Carting\Entity\Cart;
use App\Carting\Entity\CartItem;
use App\Carting\RepositoryInterface\CartRepositoryInterface;
use App\Carting\Service\Cart\CartLifecycleGuardService;
use App\Carting\Service\Cart\CartMutationService;
use App\Carting\Service\Cart\CartSummaryService;
use App\Carting\Service\Cart\CartTokenService;
use App\Carting\ServiceInterface\Cart\CartAvailabilityCheckerInterface;
use App\Carting\ServiceInterface\Cart\CartOfferProviderInterface;
use App\Carting\Snapshot\Cart\CartOfferSnapshot;
use PHPUnit\Framework\TestCase;

final class CartMutationServiceTest extends TestCase
{
    public function testAvailabilityChecksResultingQuantity(): void
    {
        $cart = new Cart('token', 'USD');
        $cart->addItem(new CartItem('offer-1', 'Offer 1', 1000, 'USD', 2));

        $availability = new class implements CartAvailabilityCheckerInterface {
            public int $checkedQuantity = 0;

            public function isAvailable(string $offerReference, int $quantity): bool
            {
                $this->checkedQuantity = $quantity;

                return false;
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

    public function testRejectsSnapshotForDifferentOffer(): void
    {
        $service = $this->createService(new class implements CartOfferProviderInterface {
            public function provideOfferSnapshot(string $offerReference, int $quantity): CartOfferSnapshot
            {
                return new CartOfferSnapshot('different-offer', 'Offer', 1000, 'USD');
            }
        });

        $this->expectException(\UnexpectedValueException::class);
        $service->addItem(new Cart('token', 'USD'), 'offer-1', 1);
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
        $service->addItem(new Cart('token', 'USD'), 'offer-1', 1);
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
        $service->addItem(new Cart('token', 'USD'), 'offer-1', 1);
    }

    private function createService(
        CartOfferProviderInterface $offerProvider,
        ?CartAvailabilityCheckerInterface $availabilityChecker = null,
    ): CartMutationService {
        $repository = $this->createStub(CartRepositoryInterface::class);
        $repository->method('save');

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
