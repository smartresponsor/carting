<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface\Cart;

use App\Carting\Value\CartMiniCartValue;
use App\Carting\Value\CartNavigationItemValue;
use App\Carting\Value\CartSurfaceActionValue;
use App\Carting\Value\CartSurfaceContract;

interface CartSurfaceProviderInterface
{
    public function provideCartSurface(string $cartToken): CartSurfaceContract;

    public function provideMiniCart(string $cartToken): CartMiniCartValue;

    /** @return list<CartNavigationItemValue> */
    public function provideNavigation(string $cartToken): array;

    /** @return list<CartSurfaceActionValue> */
    public function provideActions(string $cartToken): array;
}
