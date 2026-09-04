<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface\Cart;

use App\Carting\DTO\Cart\CartMiniCartDTO;
use App\Carting\DTO\Cart\CartNavigationItemDTO;
use App\Carting\DTO\Cart\CartSurfaceActionDTO;
use App\Carting\Contract\Cart\CartSurfaceContract;

interface CartSurfaceProviderInterface
{
    public function provideCartSurface(string $cartToken): CartSurfaceContract;

    public function provideMiniCart(string $cartToken): CartMiniCartDTO;

    /** @return list<CartNavigationItemDTO> */
    public function provideNavigation(string $cartToken): array;

    /** @return list<CartSurfaceActionDTO> */
    public function provideActions(string $cartToken): array;
}
