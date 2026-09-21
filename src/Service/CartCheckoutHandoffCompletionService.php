<?php

declare(strict_types=1);

namespace App\Carting\Service;

use App\Carting\DTO\CartCheckoutPayloadDTO;
use App\Carting\Entity\CartCheckoutHandoffEntity;
use App\Carting\Enum\CartStatus;
use App\Carting\ServiceInterface\CartCheckoutHandoffConsumerInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Defines the CartCheckoutHandoffCompletionService responsibility used by the Carting component runtime.
 */
final class CartCheckoutHandoffCompletionService
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ?CartCheckoutHandoffConsumerInterface $handoffConsumer = null,
    ) {}

    /**
     * Executes the complete behavior owned by this Carting runtime responsibility.
     */
    public function complete(CartCheckoutHandoffEntity $handoff): string
    {
        $cart = $handoff->getCart();
        $acceptedReference = $handoff->getDownstreamReference();
        if (null !== $acceptedReference) {
            if (CartStatus::Converted !== $cart->getStatus()) {
                throw new \LogicException('Accepted checkout handoff must belong to a converted cart.');
            }

            return $acceptedReference;
        }

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
