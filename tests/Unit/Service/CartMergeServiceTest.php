<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Service;

use App\Carting\Entity\CartEntity;
use App\Carting\Entity\CartItemEntity;
use App\Carting\RepositoryInterface\CartRepositoryInterface;
use App\Carting\Service\CartLifecycleGuardService;
use App\Carting\Service\CartMergeService;
use PHPUnit\Framework\TestCase;

final class CartMergeServiceTest extends TestCase
{
    public function testRecoverReturnsExistingOwnerCartWithoutGuest(): void
    {
        $owner = new CartEntity('owner', 'USD', 'owner-1');
        $repository = $this->createMock(CartRepositoryInterface::class);
        $repository->expects(self::once())->method('findActiveByOwnerReference')->with('owner-1')->willReturn($owner);
        $repository->expects(self::never())->method('save');

        $result = (new CartMergeService($repository, new CartLifecycleGuardService()))
            ->recoverOwnerCart('owner-1');

        self::assertSame($owner, $result);
    }

    public function testRecoverClaimsGuestWhenOwnerHasNoActiveCart(): void
    {
        $guest = new CartEntity('guest', 'USD');
        $repository = $this->createMock(CartRepositoryInterface::class);
        $repository->expects(self::once())->method('findActiveByOwnerReference')->with('owner-1')->willReturn(null);
        $repository->expects(self::once())->method('save')->with($guest);

        $result = (new CartMergeService($repository, new CartLifecycleGuardService()))
            ->recoverOwnerCart('owner-1', $guest);

        self::assertSame($guest, $result);
        self::assertSame('owner-1', $guest->getOwnerReference());
    }

    public function testClaimGuestCartAssignsOwnerAndPersists(): void
    {
        $guest = new CartEntity('guest', 'USD');
        $repository = $this->createMock(CartRepositoryInterface::class);
        $repository->expects(self::once())->method('save')->with($guest);

        $result = (new CartMergeService($repository, new CartLifecycleGuardService()))
            ->claimGuestCart($guest, 'owner-1');

        self::assertSame($guest, $result);
        self::assertSame('owner-1', $guest->getOwnerReference());
    }

    public function testClaimGuestCartRejectsAlreadyOwnedCart(): void
    {
        $service = new CartMergeService($this->createStub(CartRepositoryInterface::class), new CartLifecycleGuardService());

        $this->expectException(\LogicException::class);
        $service->claimGuestCart(new CartEntity('owned', 'USD', 'owner-1'), 'owner-2');
    }

    public function testMergeCopiesGuestItemAndMarksGuestCartMerged(): void
    {
        $guest = new CartEntity('guest', 'USD');
        $owner = new CartEntity('owner', 'USD', 'owner-1');
        $guestItem = new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 2, ['source' => 'guest']);
        $guest->addItem($guestItem);

        $repository = $this->createMock(CartRepositoryInterface::class);
        $repository->expects(self::exactly(2))->method('save');

        $result = (new CartMergeService($repository, new CartLifecycleGuardService()))
            ->mergeGuestCartIntoOwnerCart($guest, $owner);

        $ownerItem = $result->getItems()->first();
        self::assertInstanceOf(CartItemEntity::class, $ownerItem);
        self::assertNotSame($guestItem, $ownerItem);
        self::assertSame($guest, $guestItem->getCart());
        self::assertSame($owner, $ownerItem->getCart());
        self::assertSame('owner-1', $owner->getOwnerReference());
        self::assertSame('merged', $guest->getStatus()->value);
    }

    public function testMergeCombinesMatchingCommercialSnapshotQuantity(): void
    {
        $guest = new CartEntity('guest', 'USD');
        $owner = new CartEntity('owner', 'USD', 'owner-1');
        $guest->addItem(new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 2, ['revision' => 'same']));
        $owner->addItem(new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 3, ['revision' => 'same']));

        $repository = $this->createStub(CartRepositoryInterface::class);
        $repository->method('save');

        $result = (new CartMergeService($repository, new CartLifecycleGuardService()))
            ->mergeGuestCartIntoOwnerCart($guest, $owner);

        self::assertCount(1, $result->getItems());
        self::assertSame(5, $result->getItems()->first()->getQuantity());
    }

    public function testMergePreservesConflictingSnapshotsAsSeparateOwnerLines(): void
    {
        $guest = new CartEntity('guest', 'USD');
        $owner = new CartEntity('owner', 'USD', 'owner-1');
        $guest->addItem(new CartItemEntity('offer-1', 'Offer 1', 900, 'USD', 2, ['priceRevision' => 'guest']));
        $owner->addItem(new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 3, ['priceRevision' => 'owner']));

        $repository = $this->createStub(CartRepositoryInterface::class);
        $repository->method('save');

        $result = (new CartMergeService($repository, new CartLifecycleGuardService()))
            ->mergeGuestCartIntoOwnerCart($guest, $owner);

        self::assertCount(2, $result->getItems());
        self::assertSame([1000, 900], array_map(
            static fn(CartItemEntity $item): int => $item->getUnitPriceMinor(),
            $result->getItems()->toArray(),
        ));
        self::assertSame([3, 2], array_map(
            static fn(CartItemEntity $item): int => $item->getQuantity(),
            $result->getItems()->toArray(),
        ));
        self::assertSame('merged', $guest->getStatus()->value);
    }

    public function testMergeRejectsSameCart(): void
    {
        $cart = new CartEntity('token', 'USD');
        $service = new CartMergeService($this->createStub(CartRepositoryInterface::class), new CartLifecycleGuardService());

        $this->expectException(\InvalidArgumentException::class);
        $service->mergeGuestCartIntoOwnerCart($cart, $cart);
    }

    public function testMergeRejectsDifferentCurrencies(): void
    {
        $service = new CartMergeService($this->createStub(CartRepositoryInterface::class), new CartLifecycleGuardService());

        $this->expectException(\LogicException::class);
        $service->mergeGuestCartIntoOwnerCart(new CartEntity('guest', 'USD'), new CartEntity('owner', 'EUR', 'owner-1'));
    }

    public function testMergeRejectsOwnedSourceCart(): void
    {
        $service = new CartMergeService($this->createStub(CartRepositoryInterface::class), new CartLifecycleGuardService());

        $this->expectException(\LogicException::class);
        $service->mergeGuestCartIntoOwnerCart(
            new CartEntity('guest', 'USD', 'owner-1'),
            new CartEntity('owner', 'USD', 'owner-2'),
        );
    }

    public function testMergeRejectsGuestTargetCart(): void
    {
        $service = new CartMergeService($this->createStub(CartRepositoryInterface::class), new CartLifecycleGuardService());

        $this->expectException(\LogicException::class);
        $service->mergeGuestCartIntoOwnerCart(new CartEntity('guest', 'USD'), new CartEntity('owner', 'USD'));
    }

    public function testMergeRejectsConvertedGuestCart(): void
    {
        $guest = new CartEntity('guest', 'USD');
        $guest->markCheckoutPending();
        $guest->markConverted();
        $service = new CartMergeService($this->createStub(CartRepositoryInterface::class), new CartLifecycleGuardService());

        $this->expectException(\LogicException::class);
        $service->mergeGuestCartIntoOwnerCart($guest, new CartEntity('owner', 'USD', 'owner-1'));
    }
}
