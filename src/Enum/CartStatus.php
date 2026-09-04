<?php

declare(strict_types=1);

namespace App\Carting\Enum;

enum CartStatus: string
{
    case Active = 'active';
    case CheckoutPending = 'checkout_pending';
    case Converted = 'converted';
    case Merged = 'merged';
    case Abandoned = 'abandoned';
    case Expired = 'expired';
}
