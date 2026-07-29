<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface\Cart;

use App\Carting\Value\CartCheckoutPayload;

interface CartCheckoutHandoffConsumerInterface
{
    public function consumeCartCheckoutPayload(CartCheckoutPayload $payload): string;
}
