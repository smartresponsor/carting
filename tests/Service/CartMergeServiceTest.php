<?php

declare(strict_types=1);

namespace App\Carting\Tests\Service;

use App\Carting\Entity\Cart;
use App\Carting\Entity\CartItem;
use App\Carting\RepositoryInterface\CartRepositoryInterface;
use App\Carting\Service\Cart\CartLifecycleGuardService;
use App\Carting\Service\Cart\CartMergeService;
use PHPUnit\Framework\TestCase;

final class CartMergeServiceTest extends TestCase
{
    public function testMergeCopiesGuestItemAndConvertsGuestCart(): void
    {
        $guest = new Cart('guest', 'USD');
        $owner = new Cart('owner', 'USD', 'owner-1');
        $guestItem = new CartItem('offer-1', 'Offer 1', 1000, 'USD', 2, ['source' => 'guest']);
        $guest->addItem($guestItem);

        $repository = $this->createMock(CartRepositoryInterface::class);
        $repository->expects(self::exactly(2))->method('save');

        $result = (new CartMergeService($repository, new CartLifecycleGuardService()))
            ->mergeGuestCartIntoOwnerCart($guest, $owner);

        $ownerItem = $result->getItems()->first();
        self::assertInstanceOf(CartItem::class, $ownerItem);
        self::assertNotSame($guestItem, $ownerItem);
        self::assertSame($guest, $guestItem->getCart());
        self::assertSame($owner, $ownerItem->getCart());
        self::assertSame('converted', $guest->getStatus()->value);
    }

    public function testMergeCombinesMatchingOfferQuantity(): void
    {
        $guest = new Cart('guest', 'USD');
        $owner = new Cart('owner', 'USD');
        $guest->addItem(new CartItem('offer-1', 'Offer 1', 1000, 'USD', 2));
        $owner->addItem(new CartItem('offer-1', 'Offer 1', 1000, 'USD', 3));

        $repository = $this->createStub(CartRepositoryInterface::class);
        $repository->method('save');

        $result = (new CartMergeService($repository, new CartLifecycleGuardService()))
            ->mergeGuestCartIntoOwnerCart($guest, $owner);

        self::assertCount(1, $result->getItems());
        self::assertSame(5, $result->getItems()->first()->getQuantity());
    }

    public function testMergeRejectsSameCart(): void
    {
        $cart = new Cart('token', 'USD');
        $service = new CartMergeService($this->createStub(CartRepositoryInterface::class), new CartLifecycleGuardService());

        $this->expectException(\InvalidArgumentException::class);
        $service->mergeGuestCartIntoOwnerCart($cart, $cart);
    }

    public function testMergeRejectsDifferentCurrencies(): void
    {
        $service = new CartMergeService($this->createStub(CartRepositoryInterface::class), new CartLifecycleGuardService());

        $this->expectException(\LogicException::class);
        $service->mergeGuestCartIntoOwnerCart(new Cart('guest', 'USD'), new Cart('owner', 'EUR'));
    }

    public function testMergeRejectsConvertedGuestCart(): void
    {
        $guest = new Cart('guest', 'USD');
        $guest->markConverted();
        $service = new CartMergeService($this->createStub(CartRepositoryInterface::class), new CartLifecycleGuardService());

        $this->expectException(\LogicException::class);
        $service->mergeGuestCartIntoOwnerCart($guest, new Cart('owner', 'USD'));
    }
}
