<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Service\Cart;

use App\Carting\DTO\Cart\CartCheckoutPayloadDTO;
use App\Carting\Entity\Cart;
use App\Carting\Entity\CartCheckoutHandoffEntity;
use App\Carting\Service\Cart\CartCheckoutHandoffCompletionService;
use App\Carting\ServiceInterface\Cart\CartCheckoutHandoffConsumerInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class CartCheckoutHandoffCompletionServiceTest extends TestCase
{
    public function testCompleteRecordsAcceptanceAndConvertsCart(): void
    {
        $cart = new Cart('token', 'USD', 'owner-1');
        $cart->markCheckoutPending();
        $handoff = new CartCheckoutHandoffEntity($cart, 'handoff-1', (new CartCheckoutPayloadDTO('token', 'owner-1', 'USD', 1000, 1000, []))->toArray());
        $consumer = new class implements CartCheckoutHandoffConsumerInterface {
            public function consumeCartCheckoutPayload(CartCheckoutPayloadDTO $payload): string
            {
                return 'order-123';
            }
        };
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::exactly(2))->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $reference = (new CartCheckoutHandoffCompletionService($entityManager, $consumer))->complete($handoff);

        self::assertSame('order-123', $reference);
        self::assertSame('order-123', $handoff->getDownstreamReference());
        self::assertNotNull($handoff->getAcceptedAt());
        self::assertSame('converted', $cart->getStatus()->value);
    }

    public function testCompleteFailsClosedWithoutConsumer(): void
    {
        $cart = new Cart('token', 'USD');
        $cart->markCheckoutPending();
        $handoff = new CartCheckoutHandoffEntity($cart, 'handoff-1', (new CartCheckoutPayloadDTO('token', null, 'USD', 0, 0, []))->toArray());
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('consumer is not configured');

        (new CartCheckoutHandoffCompletionService($entityManager))->complete($handoff);
    }
}
