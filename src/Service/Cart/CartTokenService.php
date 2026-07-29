<?php

declare(strict_types=1);

namespace App\Carting\Service\Cart;

final class CartTokenService
{
    public function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
