<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface\Cart;

use App\Carting\DTO\Cart\CartCheckoutPayloadDTO;

interface CartCheckoutHandoffConsumerInterface
{
    public function consumeCartCheckoutPayload(CartCheckoutPayloadDTO $payload): string;
}
