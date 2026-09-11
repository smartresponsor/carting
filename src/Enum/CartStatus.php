<?php

declare(strict_types=1);

namespace App\Carting\Enum;

/**
 * Defines the CartStatus responsibility used by the Carting component runtime.
 */
enum CartStatus: string
{
    case Active = 'active';
    case CheckoutPending = 'checkout_pending';
    case Converted = 'converted';
    case Merged = 'merged';
    case Abandoned = 'abandoned';
    case Expired = 'expired';
}
