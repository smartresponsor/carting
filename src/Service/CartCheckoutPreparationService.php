<?php

declare(strict_types=1);

namespace App\Carting\Service;

use App\Carting\Entity\Cart;
use App\Carting\Entity\CartCheckoutHandoffEntity;
use App\Carting\Enum\CartStatus;
use App\Carting\DTO\CartAdjustmentViewDTO;
use App\Carting\DTO\CartCheckoutPayloadDTO;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Defines the CartCheckoutPreparationService responsibility used by the Carting component runtime.
 */
final class CartCheckoutPreparationService
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(
        private readonly CartSummaryService $summaryService,
        private readonly CartCheckoutReadinessService $readinessService,
        private readonly CartAdjustmentEstimateService $adjustmentEstimateService,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Executes the prepare behavior owned by this Carting runtime responsibility.
     */
    public function prepare(Cart $cart): CartCheckoutHandoffEntity
    {
        if (CartStatus::CheckoutPending === $cart->getStatus()) {
            $existingHandoff = $this->entityManager
                ->getRepository(CartCheckoutHandoffEntity::class)
                ->findOneBy(['cart' => $cart]);

            if ($existingHandoff instanceof CartCheckoutHandoffEntity) {
                return $existingHandoff;
            }

            throw new \LogicException('Checkout-pending cart has no persisted handoff.');
        }

        $readiness = $this->readinessService->inspect($cart);

        if (!$readiness->ready) {
            throw new \LogicException(implode(' ', $readiness->messages));
        }

        $this->adjustmentEstimateService->refresh($cart);
        $summary = $this->summaryService->summarize($cart);
        if ($summary->totalMinor < 0) {
            throw new \LogicException('Cart total cannot be negative after applying estimates.');
        }
        $lines = [];

        foreach ($summary->items as $item) {
            $lines[] = [
                'offerReference' => $item->offerReference,
                'title' => $item->title,
                'quantity' => $item->quantity,
                'unitPriceMinor' => $item->unitPriceMinor,
                'lineTotalMinor' => $item->lineTotalMinor,
            ];
        }

        $payload = new CartCheckoutPayloadDTO(
            $cart->getCartToken(),
            $cart->getOwnerReference(),
            $cart->getCurrencyCode(),
            $summary->subtotalMinor,
            $summary->totalMinor,
            $lines,
            array_map(static fn(CartAdjustmentViewDTO $adjustment): array => $adjustment->toArray(), $summary->adjustments),
        );

        $handoff = new CartCheckoutHandoffEntity($cart, bin2hex(random_bytes(24)), $payload->toArray());
        $cart->markCheckoutPending();
        $this->entityManager->persist($handoff);
        $this->entityManager->flush();

        return $handoff;
    }
}
