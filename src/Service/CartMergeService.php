<?php

declare(strict_types=1);

namespace App\Carting\Service;

use App\Carting\Entity\CartEntity;
use App\Carting\Entity\CartItemEntity;
use App\Carting\RepositoryInterface\CartRepositoryInterface;

/**
 * Defines the CartMergeService responsibility used by the Carting component runtime.
 */
final class CartMergeService
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartLifecycleGuardService $lifecycleGuard,
    ) {}

    /**
     * Recovers the persisted active cart for an authenticated owner and reconciles an optional guest cart.
     */
    public function recoverOwnerCart(string $ownerReference, ?CartEntity $guestCart = null): ?CartEntity
    {
        $ownerReference = trim($ownerReference);
        if ('' === $ownerReference) {
            throw new \InvalidArgumentException('Cart owner reference must not be empty.');
        }

        $ownerCart = $this->cartRepository->findActiveByOwnerReference($ownerReference);
        if (!$guestCart instanceof CartEntity) {
            return $ownerCart;
        }

        if ($ownerCart instanceof CartEntity) {
            return $this->mergeGuestCartIntoOwnerCart($guestCart, $ownerCart);
        }

        return $this->claimGuestCart($guestCart, $ownerReference);
    }

    /**
     * Reassociates an active guest cart with an authenticated owner when no owner cart exists.
     */
    public function claimGuestCart(CartEntity $guestCart, string $ownerReference): CartEntity
    {
        $this->lifecycleGuard->assertActive($guestCart, 'claim');
        $this->assertGuestCart($guestCart);

        $guestCart->assignOwner($ownerReference);
        $this->cartRepository->save($guestCart);

        return $guestCart;
    }

    /**
     * Executes the mergeGuestCartIntoOwnerCart behavior owned by this Carting runtime responsibility.
     */
    public function mergeGuestCartIntoOwnerCart(CartEntity $guestCart, CartEntity $ownerCart): CartEntity
    {
        if ($guestCart === $ownerCart) {
            throw new \InvalidArgumentException('Guest cart and owner cart must be different carts.');
        }

        $this->lifecycleGuard->assertActive($guestCart, 'merge');
        $this->lifecycleGuard->assertActive($ownerCart, 'merge into');
        $this->assertGuestCart($guestCart);

        if (null === $ownerCart->getOwnerReference()) {
            throw new \LogicException('Owner cart must have an owner reference before a guest cart can be merged into it.');
        }

        if ($guestCart->getCurrencyCode() !== $ownerCart->getCurrencyCode()) {
            throw new \LogicException('Cannot merge carts with different currencies.');
        }

        foreach ($guestCart->getItems() as $guestItem) {
            $matched = false;
            foreach ($ownerCart->getItems() as $ownerItem) {
                if ($this->hasSameCommercialSnapshot($guestItem, $ownerItem)) {
                    $ownerItem->increaseBy($guestItem->getQuantity());
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                $ownerCart->addItem(new CartItemEntity(
                    $guestItem->getOfferReference(),
                    $guestItem->getTitleSnapshot(),
                    $guestItem->getUnitPriceMinor(),
                    $guestItem->getCurrencyCode(),
                    $guestItem->getQuantity(),
                    $guestItem->getMetadata(),
                ));
            }
        }

        $guestCart->markMerged();
        $ownerCart->touch();
        $this->cartRepository->save($ownerCart);
        $this->cartRepository->save($guestCart);

        return $ownerCart;
    }

    private function assertGuestCart(CartEntity $cart): void
    {
        if (null !== $cart->getOwnerReference()) {
            throw new \LogicException('Guest cart must not already have an owner reference.');
        }
    }

    private function hasSameCommercialSnapshot(CartItemEntity $left, CartItemEntity $right): bool
    {
        return $left->getOfferReference() === $right->getOfferReference()
            && $left->getTitleSnapshot() === $right->getTitleSnapshot()
            && $left->getUnitPriceMinor() === $right->getUnitPriceMinor()
            && $left->getCurrencyCode() === $right->getCurrencyCode()
            && $left->getMetadata() === $right->getMetadata();
    }
}
