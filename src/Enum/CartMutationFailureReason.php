<?php

declare(strict_types=1);

namespace App\Carting\Enum;

/**
 * Identifies a non-success mutation outcome without coupling Carting services to HTTP status codes.
 */
enum CartMutationFailureReason: string
{
    case NotFound = 'not_found';
    case Unavailable = 'unavailable';
}
