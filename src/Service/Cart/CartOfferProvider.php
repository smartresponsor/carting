<?php

declare(strict_types=1);

namespace App\Carting\Service\Cart;

use App\Carting\ServiceInterface\Cart\CartOfferProviderInterface;
use App\Carting\Value\CartOfferSnapshot;

final class CartOfferProvider implements CartOfferProviderInterface
{
    public function provideOfferSnapshot(string $offerReference, int $quantity): CartOfferSnapshot
    {
        $normalized = trim($offerReference);
        $title = '' !== $normalized ? ucwords(str_replace(['-', '_', '.'], ' ', $normalized)) : 'Cart offer';
        $unitPriceMinor = max(0, 1000 + (abs((int) crc32($offerReference)) % 9000));

        return new CartOfferSnapshot(
            $offerReference,
            $title,
            $unitPriceMinor,
            'USD',
            [
                'quantity' => $quantity,
                'source' => 'carting.placeholder',
            ],
        );
    }
}
