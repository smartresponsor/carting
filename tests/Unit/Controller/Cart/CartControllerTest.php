<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Controller\Cart;

use App\Carting\Controller\CartController;
use App\Carting\Entity\Cart;
use App\Carting\RepositoryInterface\CartRepositoryInterface;
use App\Carting\Service\Cart\CartAdjustmentEstimateService;
use App\Carting\Service\Cart\CartCheckoutPreparationService;
use App\Carting\Service\Cart\CartCheckoutReadinessService;
use App\Carting\Service\Cart\CartLifecycleGuardService;
use App\Carting\Service\Cart\CartMutationService;
use App\Carting\Service\Cart\CartSummaryService;
use App\Carting\Service\Cart\CartSurfaceContractFactory;
use App\Carting\Service\Cart\CartTokenService;
use App\Carting\ServiceInterface\Cart\CartOfferProviderInterface;
use App\Carting\Snapshot\Cart\CartOfferSnapshot;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CartControllerTest extends TestCase
{
    public function testUnknownCartTokenReturnsNotFoundInsteadOfCreatingReplacementCart(): void
    {
        $repository = $this->createStub(CartRepositoryInterface::class);
        $repository->method('findActiveByToken')->willReturn(null);
        $request = Request::create('/cart', 'GET');
        $request->headers->set('X-Cart-Token', 'unknown');

        $this->expectException(NotFoundHttpException::class);

        $this->createController($repository)->show($request);
    }

    public function testMutationResponseUsesExplicitArrayContract(): void
    {
        $cart = new Cart('token', 'USD');
        $repository = $this->createStub(CartRepositoryInterface::class);
        $repository->method('findActiveByToken')->willReturn($cart);
        $request = Request::create(
            '/cart/items',
            'POST',
            server: ['HTTP_X_CART_TOKEN' => 'token'],
            content: json_encode(['offerReference' => 'offer-1', 'quantity' => 2], JSON_THROW_ON_ERROR),
        );

        $response = $this->createController($repository)->addItem($request);
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['changed']);
        self::assertSame('token', $payload['summary']['cartToken']);
        self::assertSame('offer-1', $payload['summary']['items'][0]['offerReference']);
        self::assertSame(2000, $payload['summary']['totalMinor']);
    }

    private function createController(CartRepositoryInterface $repository): CartController
    {
        $summaryService = new CartSummaryService();
        $lifecycleGuard = new CartLifecycleGuardService();
        $offerProvider = new class implements CartOfferProviderInterface {
            public function provideOfferSnapshot(string $offerReference, int $quantity): CartOfferSnapshot
            {
                return new CartOfferSnapshot($offerReference, 'Offer 1', 1000, 'USD');
            }
        };
        $mutationService = new CartMutationService(
            $repository,
            new CartTokenService(),
            $summaryService,
            $lifecycleGuard,
            $offerProvider,
        );
        $checkoutPreparationService = new CartCheckoutPreparationService(
            $summaryService,
            new CartCheckoutReadinessService($lifecycleGuard),
            new CartAdjustmentEstimateService(),
            $this->createStub(EntityManagerInterface::class),
        );

        return new CartController(
            $repository,
            $mutationService,
            $summaryService,
            new CartSurfaceContractFactory(),
            $checkoutPreparationService,
        );
    }
}
