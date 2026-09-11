<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface;

use App\Carting\DTO\CartMiniCartDTO;
use App\Carting\DTO\CartNavigationItemDTO;
use App\Carting\DTO\CartSurfaceActionDTO;
use App\Carting\Contract\CartSurfaceContract;

/**
 * Defines the CartSurfaceProviderInterface responsibility used by the Carting component runtime.
 */
interface CartSurfaceProviderInterface
{
    /**
     * Executes the provideCartSurface behavior owned by this Carting runtime responsibility.
     */
    public function provideCartSurface(string $cartToken): CartSurfaceContract;

    /**
     * Executes the provideMiniCart behavior owned by this Carting runtime responsibility.
     */
    public function provideMiniCart(string $cartToken): CartMiniCartDTO;

    /**
     * Executes the provideNavigation behavior owned by this Carting runtime responsibility.
     * @return list<CartNavigationItemDTO>
     */
    public function provideNavigation(string $cartToken): array;

    /**
     * Executes the provideActions behavior owned by this Carting runtime responsibility.
     * @return list<CartSurfaceActionDTO>
     */
    public function provideActions(string $cartToken): array;
}
