<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Service\Cart;

use App\Carting\Entity\Cart;
use App\Carting\Entity\CartCheckoutHandoffEntity;
use App\Carting\Entity\CartItem;
use App\Carting\Service\Cart\CartAdjustmentEstimateService;
use App\Carting\Service\Cart\CartCheckoutPreparationService;
use App\Carting\Service\Cart\CartCheckoutReadinessService;
use App\Carting\Service\Cart\CartLifecycleGuardService;
use App\Carting\Service\Cart\CartSummaryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class CartCheckoutPreparationServiceTest extends TestCase
{
    public function testPreparePersistsHandoffAndMarksCartCheckoutPendingWithSingleFlush(): void
    {
        $cart = new Cart('token', 'USD', 'owner-1');
        $cart->addItem(new CartItem('offer-1', 'Offer 1', 1000, 'USD', 2));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::callback(static fn(object $entity): bool => $entity instanceof CartCheckoutHandoffEntity));
        $entityManager->expects(self::once())->method('flush');

        $handoff = $this->createService($entityManager)->prepare($cart);

        self::assertSame('checkout_pending', $cart->getStatus()->value);
        self::assertSame('token', $handoff->getPayload()['cartToken']);
        self::assertSame('owner-1', $handoff->getPayload()['ownerReference']);
        self::assertSame(2000, $handoff->getPayload()['totalMinor']);
    }

    public function testPrepareRejectsAlreadyConvertedCart(): void
    {
        $cart = new Cart('token', 'USD');
        $cart->addItem(new CartItem('offer-1', 'Offer 1', 1000, 'USD', 1));
        $cart->markCheckoutPending();
        $cart->markConverted();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $this->expectException(\LogicException::class);
        $this->createService($entityManager)->prepare($cart);
    }

    public function testPrepareRejectsCheckoutPendingCart(): void
    {
        $cart = new Cart('token', 'USD');
        $cart->addItem(new CartItem('offer-1', 'Offer 1', 1000, 'USD', 1));
        $cart->markCheckoutPending();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $this->expectException(\LogicException::class);
        $this->createService($entityManager)->prepare($cart);
    }

    public function testPrepareRejectsEmptyCart(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $this->expectException(\LogicException::class);
        $this->createService($entityManager)->prepare(new Cart('token', 'USD'));
    }

    private function createService(EntityManagerInterface $entityManager): CartCheckoutPreparationService
    {
        $lifecycleGuard = new CartLifecycleGuardService();

        return new CartCheckoutPreparationService(
            new CartSummaryService(),
            new CartCheckoutReadinessService($lifecycleGuard),
            new CartAdjustmentEstimateService(),
            $entityManager,
        );
    }
}
