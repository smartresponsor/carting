<?php

declare(strict_types=1);

namespace App\Carting\Enum;

/**
 * Defines the CartAdjustmentType responsibility used by the Carting component runtime.
 */
enum CartAdjustmentType: string
{
    case PriceEstimate = 'price_estimate';
    case Promotion = 'promotion';
    case TaxEstimate = 'tax_estimate';
    case ShippingEstimate = 'shipping_estimate';
    case Manual = 'manual';
}
