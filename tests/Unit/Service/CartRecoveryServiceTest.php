<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Service;

use App\Carting\Entity\Cart;
use App\Carting\RepositoryInterface\CartRepositoryInterface;
use App\Carting\Service\CartRecoveryService;
use PHPUnit\Framework\TestCase;

final class CartRecoveryServiceTest extends TestCase
{
    public function testRecoversPersistedActiveOwnerCart(): void
    {
        $cart = new Cart('saved-token', 'USD', 'owner-1');
        $repository = $this->createMock(CartRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findActiveByOwnerReference')
            ->with('owner-1')
            ->willReturn($cart);

        $result = (new CartRecoveryService($repository))->recoverActiveOwnerCart(' owner-1 ');

        self::assertSame($cart, $result);
    }

    public function testReturnsNullWhenOwnerHasNoActiveSavedCart(): void
    {
        $repository = $this->createStub(CartRepositoryInterface::class);
        $repository->method('findActiveByOwnerReference')->willReturn(null);

        self::assertNull((new CartRecoveryService($repository))->recoverActiveOwnerCart('owner-1'));
    }

    public function testRejectsEmptyOwnerReference(): void
    {
        $service = new CartRecoveryService($this->createStub(CartRepositoryInterface::class));

        $this->expectException(\InvalidArgumentException::class);
        $service->recoverActiveOwnerCart('   ');
    }
}
