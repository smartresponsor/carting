<?php

declare(strict_types=1);

namespace App\Carting\Controller;

use App\Carting\Entity\Cart;
use App\Carting\RepositoryInterface\CartRepositoryInterface;
use App\Carting\Service\CartCheckoutPreparationService;
use App\Carting\Service\CartMutationService;
use App\Carting\Service\CartSummaryService;
use App\Carting\Factory\CartSurfaceContractFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/cart')]
/**
 * Defines the CartController responsibility used by the Carting component runtime.
 */
final class CartController
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartMutationService $mutationService,
        private readonly CartSummaryService $summaryService,
        private readonly CartSurfaceContractFactory $surfaceContractFactory,
        private readonly CartCheckoutPreparationService $checkoutPreparationService,
    ) {}

    #[Route('', name: 'carting_cart_show', methods: ['GET'])]
    #[Route('/', name: 'carting_cart_show_slash', methods: ['GET'])]
    /**
     * Executes the show behavior owned by this Carting runtime responsibility.
     */
    public function show(Request $request): mixed
    {
        $cart = $this->resolveCart($request);

        return $this->surfaceContractFactory->createSummarySurface($this->summaryService->summarize($cart));
    }

    #[Route('/items', name: 'carting_cart_add_item', methods: ['POST'])]
    /**
     * Executes the addItem behavior owned by this Carting runtime responsibility.
     */
    public function addItem(Request $request): JsonResponse
    {
        $payload = $this->decodeMutationPayload($request);
        if (!isset($payload['offerReference']) || !is_string($payload['offerReference']) || '' === trim($payload['offerReference'])) {
            throw new BadRequestHttpException('offerReference must be a non-empty string.');
        }

        $quantity = $payload['quantity'] ?? 1;
        if (!is_int($quantity) || $quantity < 1) {
            throw new BadRequestHttpException('quantity must be a positive integer.');
        }

        $cart = $this->resolveCart($request);
        $result = $this->mutationService->addItem($cart, $payload['offerReference'], $quantity);

        return new JsonResponse($result->toArray(), $result->changed ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Route('/items/{id}', name: 'carting_cart_update_item', methods: ['PATCH'])]
    /**
     * Executes the updateItem behavior owned by this Carting runtime responsibility.
     */
    public function updateItem(Request $request, int $id): JsonResponse
    {
        $payload = $this->decodeMutationPayload($request);
        if (!isset($payload['quantity']) || !is_int($payload['quantity']) || $payload['quantity'] < 1) {
            throw new BadRequestHttpException('quantity must be a positive integer.');
        }

        $cart = $this->resolveCart($request);
        $result = $this->mutationService->updateItemQuantity($cart, $id, $payload['quantity']);

        return new JsonResponse($result->toArray(), $result->changed ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    #[Route('/items/{id}', name: 'carting_cart_remove_item', methods: ['DELETE'])]
    /**
     * Executes the removeItem behavior owned by this Carting runtime responsibility.
     */
    public function removeItem(Request $request, int $id): JsonResponse
    {
        $cart = $this->resolveCart($request);
        $result = $this->mutationService->removeItem($cart, $id);

        return new JsonResponse($result->toArray(), $result->changed ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    #[Route('/checkout', name: 'carting_cart_checkout', methods: ['POST'])]
    /**
     * Executes the checkout behavior owned by this Carting runtime responsibility.
     */
    public function checkout(Request $request): JsonResponse
    {
        $cart = $this->resolveCart($request);
        $handoff = $this->checkoutPreparationService->prepare($cart);

        return new JsonResponse(['handoffReference' => $handoff->getHandoffReference()]);
    }

    /**
     * Validates the JSON object before a mutation can create or change a cart.
     *
     * @return array<string, mixed>
     */
    private function decodeMutationPayload(Request $request): array
    {
        try {
            $payload = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new BadRequestHttpException('Request body must be valid JSON.', $exception);
        }

        if (!$payload instanceof \stdClass) {
            throw new BadRequestHttpException('Request body must be a JSON object.');
        }

        return (array) $payload;
    }

    /**
     * Returns the active cart, creating one only when no cart token is supplied.
     */
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
