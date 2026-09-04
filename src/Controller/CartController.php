<?php

declare(strict_types=1);

namespace App\Carting\Controller;

use App\Carting\Entity\Cart;
use App\Carting\RepositoryInterface\CartRepositoryInterface;
use App\Carting\Service\Cart\CartCheckoutPreparationService;
use App\Carting\Service\Cart\CartMutationService;
use App\Carting\Service\Cart\CartSummaryService;
use App\Carting\Service\Cart\CartSurfaceContractFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/cart')]
final class CartController
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartMutationService $mutationService,
        private readonly CartSummaryService $summaryService,
        private readonly CartSurfaceContractFactory $surfaceContractFactory,
        private readonly CartCheckoutPreparationService $checkoutPreparationService,
    ) {}

    #[Route('', name: 'carting_cart_show', methods: ['GET'])]
    #[Route('/', name: 'carting_cart_show_slash', methods: ['GET'])]
    public function show(Request $request): mixed
    {
        $cart = $this->resolveCart($request);

        return $this->surfaceContractFactory->createSummarySurface($this->summaryService->summarize($cart));
    }

    #[Route('/items', name: 'carting_cart_add_item', methods: ['POST'])]
    public function addItem(Request $request): JsonResponse
    {
        $cart = $this->resolveCart($request);
        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $result = $this->mutationService->addItem($cart, (string) $payload['offerReference'], (int) ($payload['quantity'] ?? 1));

        return new JsonResponse($result->toArray(), $result->changed ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Route('/items/{id}', name: 'carting_cart_update_item', methods: ['PATCH'])]
    public function updateItem(Request $request, int $id): JsonResponse
    {
        $cart = $this->resolveCart($request);
        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $result = $this->mutationService->updateItemQuantity($cart, $id, (int) $payload['quantity']);

        return new JsonResponse($result->toArray(), $result->changed ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    #[Route('/items/{id}', name: 'carting_cart_remove_item', methods: ['DELETE'])]
    public function removeItem(Request $request, int $id): JsonResponse
    {
        $cart = $this->resolveCart($request);
        $result = $this->mutationService->removeItem($cart, $id);

        return new JsonResponse($result->toArray(), $result->changed ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    #[Route('/checkout', name: 'carting_cart_checkout', methods: ['POST'])]
    public function checkout(Request $request): JsonResponse
    {
        $cart = $this->resolveCart($request);
        $handoff = $this->checkoutPreparationService->prepare($cart);

        return new JsonResponse(['handoffReference' => $handoff->getHandoffReference()]);
    }

    private function resolveCart(Request $request): Cart
    {
        $cartToken = (string) $request->headers->get('X-Cart-Token', '');
        if ('' === $cartToken) {
            return $this->mutationService->create('USD');
        }

        $cart = $this->cartRepository->findActiveByToken($cartToken);
        if (!$cart instanceof Cart) {
            throw new NotFoundHttpException('Active cart was not found for the provided cart token.');
        }

        return $cart;
    }
}
