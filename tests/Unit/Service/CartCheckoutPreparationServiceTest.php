<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Service;

use App\Carting\Entity\CartEntity;
use App\Carting\Entity\CartCheckoutHandoffEntity;
use App\Carting\Entity\CartItemEntity;
use App\Carting\Service\CartAdjustmentEstimateService;
use App\Carting\Service\CartCheckoutPreparationService;
use App\Carting\Service\CartCheckoutReadinessService;
use App\Carting\Service\CartLifecycleGuardService;
use App\Carting\Service\CartSummaryService;
use App\Carting\RepositoryInterface\CartCheckoutHandoffRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CartCheckoutPreparationServiceTest extends TestCase
{
    public function testPreparePersistsHandoffAndMarksCartCheckoutPending(): void
    {
        $cart = new CartEntity('token', 'USD', 'owner-1');
        $cart->addItem(new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 2));

        $handoffRepository = $this->createMock(CartCheckoutHandoffRepositoryInterface::class);
        $handoffRepository
            ->expects(self::once())
            ->method('savePrepared')
            ->with(self::callback(static fn(object $entity): bool => $entity instanceof CartCheckoutHandoffEntity));

        $handoff = $this->createService($handoffRepository)->prepare($cart);

        self::assertSame('checkout_pending', $cart->getStatus()->value);
        self::assertSame('token', $handoff->getPayload()['cartToken']);
        self::assertSame('owner-1', $handoff->getPayload()['ownerReference']);
        self::assertSame(2000, $handoff->getPayload()['totalMinor']);
    }

    public function testPrepareRejectsAlreadyConvertedCart(): void
    {
        $cart = new CartEntity('token', 'USD');
        $cart->addItem(new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 1));
        $cart->markCheckoutPending();
        $cart->markConverted();

        $handoffRepository = $this->createMock(CartCheckoutHandoffRepositoryInterface::class);
        $handoffRepository->expects(self::never())->method('savePrepared');

        $this->expectException(\LogicException::class);
        $this->createService($handoffRepository)->prepare($cart);
    }

    public function testPrepareReturnsExistingHandoffForCheckoutPendingRetry(): void
    {
        $cart = new CartEntity('token', 'USD', 'owner-1');
        $cart->addItem(new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 1));
        $cart->markCheckoutPending();
        $handoff = new CartCheckoutHandoffEntity($cart, 'handoff-existing', ['cartToken' => 'token']);

        $handoffRepository = $this->createMock(CartCheckoutHandoffRepositoryInterface::class);
        $handoffRepository->method('findForCart')->with($cart)->willReturn($handoff);
        $handoffRepository->expects(self::never())->method('savePrepared');

        $result = $this->createService($handoffRepository)->prepare($cart);

        self::assertSame($handoff, $result);
        self::assertSame('handoff-existing', $result->getHandoffReference());
    }

    public function testPrepareRejectsCheckoutPendingCart(): void
    {
        $cart = new CartEntity('token', 'USD');
        $cart->addItem(new CartItemEntity('offer-1', 'Offer 1', 1000, 'USD', 1));
        $cart->markCheckoutPending();

        $handoffRepository = $this->createMock(CartCheckoutHandoffRepositoryInterface::class);
        $handoffRepository->expects(self::never())->method('savePrepared');

        $this->expectException(\LogicException::class);
        $this->createService($handoffRepository)->prepare($cart);
    }

    public function testPrepareRejectsEmptyCart(): void
    {
        $handoffRepository = $this->createMock(CartCheckoutHandoffRepositoryInterface::class);
        $handoffRepository->expects(self::never())->method('savePrepared');

        $this->expectException(\LogicException::class);
        $this->createService($handoffRepository)->prepare(new CartEntity('token', 'USD'));
    }

    private function createService(CartCheckoutHandoffRepositoryInterface $handoffRepository): CartCheckoutPreparationService
    {
        $lifecycleGuard = new CartLifecycleGuardService();

        return new CartCheckoutPreparationService(
            new CartSummaryService(),
            new CartCheckoutReadinessService($lifecycleGuard),
            new CartAdjustmentEstimateService(),
            $handoffRepository,
        );
    }
}
