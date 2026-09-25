<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Service;

use App\Carting\DTO\CartCheckoutPayloadDTO;
use App\Carting\Entity\CartEntity;
use App\Carting\Entity\CartCheckoutHandoffEntity;
use App\Carting\Service\CartCheckoutHandoffCompletionService;
use App\Carting\ServiceInterface\CartCheckoutHandoffConsumerInterface;
use App\Carting\RepositoryInterface\CartCheckoutHandoffRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CartCheckoutHandoffCompletionServiceTest extends TestCase
{
    public function testCompleteRecordsAcceptanceAndConvertsCart(): void
    {
        $cart = new CartEntity('token', 'USD', 'owner-1');
        $cart->markCheckoutPending();
        $handoff = new CartCheckoutHandoffEntity($cart, 'handoff-1', (new CartCheckoutPayloadDTO('token', 'owner-1', 'USD', 1000, 1000, []))->toArray());
        $consumer = new class implements CartCheckoutHandoffConsumerInterface {
            public function consumeCartCheckoutPayload(CartCheckoutPayloadDTO $payload): string
            {
                return 'order-123';
            }
        };
        $handoffRepository = $this->createMock(CartCheckoutHandoffRepositoryInterface::class);
        $handoffRepository->expects(self::once())->method('saveAccepted')->with($handoff);

        $reference = (new CartCheckoutHandoffCompletionService($handoffRepository, $consumer))->complete($handoff);

        self::assertSame('order-123', $reference);
        self::assertSame('order-123', $handoff->getDownstreamReference());
        self::assertNotNull($handoff->getAcceptedAt());
        self::assertSame('converted', $cart->getStatus()->value);
    }

    public function testCompleteReturnsAcceptedReferenceWithoutCallingConsumerAgain(): void
    {
        $cart = new CartEntity('token', 'USD', 'owner-1');
        $cart->markCheckoutPending();
        $handoff = new CartCheckoutHandoffEntity($cart, 'handoff-1', (new CartCheckoutPayloadDTO('token', 'owner-1', 'USD', 1000, 1000, []))->toArray());
        $handoff->markAccepted('order-123');
        $cart->markConverted();

        $consumer = $this->createMock(CartCheckoutHandoffConsumerInterface::class);
        $consumer->expects(self::never())->method('consumeCartCheckoutPayload');
        $handoffRepository = $this->createMock(CartCheckoutHandoffRepositoryInterface::class);
        $handoffRepository->expects(self::never())->method('saveAccepted');

        $reference = (new CartCheckoutHandoffCompletionService($handoffRepository, $consumer))->complete($handoff);

        self::assertSame('order-123', $reference);
    }

    public function testCompleteFailsClosedWithoutConsumer(): void
    {
        $cart = new CartEntity('token', 'USD');
        $cart->markCheckoutPending();
        $handoff = new CartCheckoutHandoffEntity($cart, 'handoff-1', (new CartCheckoutPayloadDTO('token', null, 'USD', 0, 0, []))->toArray());
        $handoffRepository = $this->createMock(CartCheckoutHandoffRepositoryInterface::class);
        $handoffRepository->expects(self::never())->method('saveAccepted');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('consumer is not configured');

        (new CartCheckoutHandoffCompletionService($handoffRepository))->complete($handoff);
    }
}
