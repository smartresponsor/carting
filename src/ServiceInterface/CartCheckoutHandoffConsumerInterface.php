<?php

declare(strict_types=1);

namespace App\Carting\ServiceInterface;

use App\Carting\DTO\CartCheckoutPayloadDTO;

/**
 * Defines the CartCheckoutHandoffConsumerInterface responsibility used by the Carting component runtime.
 */
interface CartCheckoutHandoffConsumerInterface
{
    /**
     * Executes the consumeCartCheckoutPayload behavior owned by this Carting runtime responsibility.
     */
    public function consumeCartCheckoutPayload(CartCheckoutPayloadDTO $payload): string;
}
