<?php

declare(strict_types=1);

namespace App\Carting\Service\Cart;

use App\Carting\Entity\Cart;
use App\Carting\Repository\CartRepository;
use App\Carting\ServiceInterface\Cart\CartSurfaceProviderInterface;
use App\Carting\Value\CartMiniCartValue;
use App\Carting\Value\CartNavigationItemValue;
use App\Carting\Value\CartSurfaceActionValue;
use App\Carting\Value\CartSurfaceContract;

final class CartSurfaceProvider implements CartSurfaceProviderInterface
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly CartSummaryService $summaryService,
        private readonly CartSurfaceContractFactory $surfaceContractFactory,
    ) {}

    public function provideCartSurface(string $cartToken): CartSurfaceContract
    {
        return $this->surfaceContractFactory->createSummarySurface(
            $this->summaryService->summarize($this->resolveActiveCart($cartToken)),
        );
    }

    public function provideMiniCart(string $cartToken): CartMiniCartValue
    {
        $summary = $this->summaryService->summarize($this->resolveActiveCart($cartToken));

        return $this->surfaceContractFactory->createMiniCart($summary);
    }

    /** @return list<CartNavigationItemValue> */
    public function provideNavigation(string $cartToken): array
    {
        $summary = $this->summaryService->summarize($this->resolveActiveCart($cartToken));

        return $this->surfaceContractFactory->createNavigation($summary);
    }

    /** @return list<CartSurfaceActionValue> */
    public function provideActions(string $cartToken): array
    {
        $summary = $this->summaryService->summarize($this->resolveActiveCart($cartToken));

        return $this->surfaceContractFactory->createActions($summary);
    }

    private function resolveActiveCart(string $cartToken): Cart
    {
        $cart = $this->cartRepository->findActiveByToken($cartToken);
        if (!$cart) {
            throw new \RuntimeException('Active cart was not found for the provided token.');
        }

        return $cart;
    }
}
