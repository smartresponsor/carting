<?php

declare(strict_types=1);

namespace App\Carting\Service;

/**
 * Defines the CartTokenService responsibility used by the Carting component runtime.
 */
final class CartTokenService
{
    /**
     * Executes the generateToken behavior owned by this Carting runtime responsibility.
     */
    public function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
