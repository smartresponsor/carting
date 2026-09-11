<?php

declare(strict_types=1);

namespace App\Carting\Provider;

use App\Carting\Entity\Cart;
use App\Carting\RepositoryInterface\CartRepositoryInterface;
use App\Carting\ServiceInterface\CartSurfaceProviderInterface;
use App\Carting\DTO\CartMiniCartDTO;
use App\Carting\DTO\CartNavigationItemDTO;
use App\Carting\DTO\CartSurfaceActionDTO;
use App\Carting\Contract\CartSurfaceContract;
use App\Carting\Factory\CartSurfaceContractFactory;
use App\Carting\Service\CartSummaryService;

/**
 * Defines the CartSurfaceProvider responsibility used by the Carting component runtime.
 */
final class CartSurfaceProvider implements CartSurfaceProviderInterface
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartSummaryService $summaryService,
        private readonly CartSurfaceContractFactory $surfaceContractFactory,
    ) {}

    /**
     * Executes the provideCartSurface behavior owned by this Carting runtime responsibility.
     */
    public function provideCartSurface(string $cartToken): CartSurfaceContract
    {
        return $this->surfaceContractFactory->createSummarySurface(
            $this->summaryService->summarize($this->resolveActiveCart($cartToken)),
        );
    }

    /**
     * Executes the provideMiniCart behavior owned by this Carting runtime responsibility.
     */
    public function provideMiniCart(string $cartToken): CartMiniCartDTO
    {
        $summary = $this->summaryService->summarize($this->resolveActiveCart($cartToken));

        return $this->surfaceContractFactory->createMiniCart($summary);
    }

    /**
     * Executes the provideNavigation behavior owned by this Carting runtime responsibility.
     * @return list<CartNavigationItemDTO>
     */
    public function provideNavigation(string $cartToken): array
    {
        $summary = $this->summaryService->summarize($this->resolveActiveCart($cartToken));

        return $this->surfaceContractFactory->createNavigation($summary);
    }

    /**
     * Executes the provideActions behavior owned by this Carting runtime responsibility.
     * @return list<CartSurfaceActionDTO>
     */
    public function provideActions(string $cartToken): array
    {
        $summary = $this->summaryService->summarize($this->resolveActiveCart($cartToken));

        return $this->surfaceContractFactory->createActions($summary);
    }

    /**
     * Returns the value produced by resolveActiveCart for this Carting runtime responsibility.
     */
    private function resolveActiveCart(string $cartToken): Cart
    {
        $cart = $this->cartRepository->findActiveByToken($cartToken);
        if (!$cart) {
            throw new \RuntimeException('Active cart was not found for the provided token.');
        }

        return $cart;
    }
}
