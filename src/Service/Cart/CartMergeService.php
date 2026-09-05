<?php

declare(strict_types=1);

namespace App\Carting\Service\Cart;

use App\Carting\Entity\Cart;
use App\Carting\Entity\CartItem;
use App\Carting\RepositoryInterface\CartRepositoryInterface;

final class CartMergeService
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartLifecycleGuardService $lifecycleGuard,
    ) {}

    public function mergeGuestCartIntoOwnerCart(Cart $guestCart, Cart $ownerCart): Cart
    {
        if ($guestCart === $ownerCart) {
            throw new \InvalidArgumentException('Guest cart and owner cart must be different carts.');
        }

        $this->lifecycleGuard->assertActive($guestCart, 'merge');
        $this->lifecycleGuard->assertActive($ownerCart, 'merge into');

        if ($guestCart->getCurrencyCode() !== $ownerCart->getCurrencyCode()) {
            throw new \LogicException('Cannot merge carts with different currencies.');
        }

        foreach ($guestCart->getItems() as $guestItem) {
            $matched = false;
            foreach ($ownerCart->getItems() as $ownerItem) {
                if ($ownerItem->getOfferReference() === $guestItem->getOfferReference()) {
                    $ownerItem->increaseBy($guestItem->getQuantity());
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                $ownerCart->addItem(new CartItem(
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
}
