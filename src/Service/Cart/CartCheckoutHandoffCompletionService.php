<?php

declare(strict_types=1);

namespace App\Carting\Service\Cart;

use App\Carting\DTO\Cart\CartCheckoutPayloadDTO;
use App\Carting\Entity\CartCheckoutHandoffEntity;
use App\Carting\Enum\CartStatus;
use App\Carting\ServiceInterface\Cart\CartCheckoutHandoffConsumerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class CartCheckoutHandoffCompletionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ?CartCheckoutHandoffConsumerInterface $handoffConsumer = null,
    ) {}

    public function complete(CartCheckoutHandoffEntity $handoff): string
    {
        $cart = $handoff->getCart();
        if (CartStatus::CheckoutPending !== $cart->getStatus()) {
            throw new \LogicException('Only a checkout-pending cart can complete a checkout handoff.');
        }

        if (!$this->handoffConsumer instanceof CartCheckoutHandoffConsumerInterface) {
            throw new \LogicException('Cart checkout handoff consumer is not configured.');
        }

        $downstreamReference = trim($this->handoffConsumer->consumeCartCheckoutPayload(
            CartCheckoutPayloadDTO::fromArray($handoff->getPayload()),
        ));
        if ('' === $downstreamReference) {
            throw new \UnexpectedValueException('Cart checkout handoff consumer returned an empty downstream reference.');
        }

        $handoff->markAccepted($downstreamReference);
        $cart->markConverted();
        $this->entityManager->persist($handoff);
        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        return $downstreamReference;
    }
}
