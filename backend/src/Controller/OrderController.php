<?php

namespace App\Controller;

use App\Dto\CreateOrderRequest;
use App\Entity\Shop;
use App\Repository\OrderRepository;
use App\Repository\ShopRepository;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/shops/{shopId}')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly ShopRepository $shopRepository,
        private readonly OrderRepository $orderRepository,
        private readonly OrderService $orderService,
    ) {
    }

    #[Route('/orders', name: 'orders_create', methods: ['POST'])]
    public function create(
        int $shopId,
        #[MapRequestPayload] CreateOrderRequest $dto,
    ): JsonResponse {
        $shop = $this->getShopOr404($shopId);
        $result = $this->orderService->createAndNotify($shop, $dto);

        return $this->json([
            'order' => $this->serializeOrder($result['order']),
            'sendStatus' => $result['sendStatus'],
        ], Response::HTTP_CREATED);
    }

    #[Route('/orders', name: 'orders_list', methods: ['GET'])]
    public function list(int $shopId): JsonResponse
    {
        $shop = $this->getShopOr404($shopId);
        $this->denyAccessUnlessGranted('SHOP_ACCESS', $shop);

        $orders = $this->orderRepository->findByShopOrdered($shop);

        return $this->json(array_map(fn ($o) => $this->serializeOrder($o), $orders));
    }

    private function getShopOr404(int $shopId): Shop
    {
        $shop = $this->shopRepository->find($shopId);
        if ($shop === null) {
            throw $this->createNotFoundException('Shop not found');
        }

        return $shop;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeOrder(\App\Entity\Order $order): array
    {
        return [
            'id' => $order->getId(),
            'shopId' => $order->getShop()->getId(),
            'number' => $order->getNumber(),
            'total' => (float) $order->getTotal(),
            'customerName' => $order->getCustomerName(),
            'createdAt' => $order->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
