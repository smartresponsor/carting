<?php

declare(strict_types=1);

namespace App\Carting\Service;

use App\Carting\Entity\CartEntity;
use App\Carting\Entity\CartItemEntity;
use App\Carting\Enum\CartMutationFailureReason;
use App\Carting\RepositoryInterface\CartRepositoryInterface;
use App\Carting\ServiceInterface\CartAvailabilityCheckerInterface;
use App\Carting\ServiceInterface\CartOfferProviderInterface;
use App\Carting\DTO\CartMutationResultDTO;

/**
 * Defines the CartMutationService responsibility used by the Carting component runtime.
 */
final class CartMutationService
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartTokenService $cartTokenService,
        private readonly CartSummaryService $summaryService,
        private readonly CartLifecycleGuardService $lifecycleGuard,
        private readonly CartOfferProviderInterface $offerProvider,
        private readonly ?CartAvailabilityCheckerInterface $availabilityChecker = null,
    ) {}

    /**
     * Executes the create behavior owned by this Carting runtime responsibility.
     */
    public function create(string $currencyCode = 'USD', ?string $ownerReference = null): CartEntity
    {
        $cart = new CartEntity($this->cartTokenService->generateToken(), $currencyCode, $ownerReference);
        $this->cartRepository->save($cart);

        return $cart;
    }

    /**
     * Executes the addItem behavior owned by this Carting runtime responsibility.
     */
    public function addItem(CartEntity $cart, string $offerReference, int $quantity): CartMutationResultDTO
    {
        $this->lifecycleGuard->assertActive($cart, 'add item to');

        if ($quantity < 1) {
            throw new \InvalidArgumentException('Cart item quantity must be at least 1.');
        }

        $existingItem = null;
        foreach ($cart->getItems() as $item) {
            if ($item->getOfferReference() === $offerReference) {
                $existingItem = $item;
                break;
            }
        }

        $resultingQuantity = $quantity + ($existingItem?->getQuantity() ?? 0);

        if ($this->availabilityChecker && !$this->availabilityChecker->checkAvailability($offerReference, $resultingQuantity)->available) {
            return new CartMutationResultDTO(
                false,
                'Offer is not available in the requested quantity.',
                $this->summaryService->summarize($cart),
                CartMutationFailureReason::Unavailable,
            );
        }

        $snapshot = $this->offerProvider->provideOfferSnapshot($offerReference, $quantity);

        if ($snapshot->offerReference !== $offerReference) {
            throw new \UnexpectedValueException('Offer provider returned a snapshot for a different offer reference.');
        }

        if (strtoupper($snapshot->currencyCode) !== $cart->getCurrencyCode()) {
            throw new \UnexpectedValueException('Offer currency does not match cart currency.');
        }

        if ($snapshot->unitPriceMinor < 0) {
            throw new \UnexpectedValueException('Offer provider returned a negative unit price.');
        }

        if ($existingItem instanceof CartItemEntity) {
            $existingItem->increaseBy($quantity);
            $cart->touch();
            $this->cartRepository->save($cart);

            return new CartMutationResultDTO(true, 'Cart item quantity increased.', $this->summaryService->summarize($cart));
        }

        $cart->addItem(new CartItemEntity(
            $snapshot->offerReference,
            $snapshot->title,
            $snapshot->unitPriceMinor,
            $snapshot->currencyCode,
            $quantity,
            $snapshot->metadata,
        ));

        $this->cartRepository->save($cart);

        return new CartMutationResultDTO(true, 'Cart item added.', $this->summaryService->summarize($cart));
    }

    /**
     * Executes the updateItemQuantity behavior owned by this Carting runtime responsibility.
     */
    public function updateItemQuantity(CartEntity $cart, int $cartItemId, int $quantity): CartMutationResultDTO
    {
        $this->lifecycleGuard->assertActive($cart, 'update item quantity on');

        foreach ($cart->getItems() as $item) {
            if ((int) $item->getId() === $cartItemId) {
                if ($this->availabilityChecker && !$this->availabilityChecker->checkAvailability($item->getOfferReference(), $quantity)->available) {
                    return new CartMutationResultDTO(
                        false,
                        'Offer is not available in the requested quantity.',
                        $this->summaryService->summarize($cart),
                        CartMutationFailureReason::Unavailable,
                    );
                }

                $item->changeQuantity($quantity);
                $cart->touch();
                $this->cartRepository->save($cart);

                return new CartMutationResultDTO(true, 'Cart item quantity updated.', $this->summaryService->summarize($cart));
            }
        }

        return new CartMutationResultDTO(
            false,
            'Cart item was not found.',
            $this->summaryService->summarize($cart),
            CartMutationFailureReason::NotFound,
        );
    }

    /**
     * Executes the removeItem behavior owned by this Carting runtime responsibility.
     */
    public function removeItem(CartEntity $cart, int $cartItemId): CartMutationResultDTO
    {
        $this->lifecycleGuard->assertActive($cart, 'remove item from');

        foreach ($cart->getItems() as $item) {
            if ((int) $item->getId() === $cartItemId) {
                $cart->removeItem($item);
                $this->cartRepository->save($cart);

                return new CartMutationResultDTO(true, 'Cart item removed.', $this->summaryService->summarize($cart));
            }
        }

        return new CartMutationResultDTO(
            false,
            'Cart item was not found.',
            $this->summaryService->summarize($cart),
            CartMutationFailureReason::NotFound,
        );
    }
}
