<?php

namespace App\Controller;

use App\Dto\TelegramConnectRequest;
use App\Entity\Shop;
use App\Repository\ShopRepository;
use App\Service\TelegramIntegrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/shops/{shopId}/telegram')]
class TelegramController extends AbstractController
{
    public function __construct(
        private readonly ShopRepository $shopRepository,
        private readonly TelegramIntegrationService $integrationService,
    ) {
    }

    #[Route('/connect', name: 'telegram_connect', methods: ['POST'])]
    public function connect(
        int $shopId,
        #[MapRequestPayload] TelegramConnectRequest $dto,
    ): JsonResponse {
        $shop = $this->getShopOr404($shopId);
        $this->denyAccessUnlessGranted('SHOP_ACCESS', $shop);

        try {
            $integration = $this->integrationService->connect($shop, $dto);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }

        return $this->json([
            'shopId' => $shop->getId(),
            'chatId' => $integration->getChatId(),
            'enabled' => $integration->isEnabled(),
            'hasBotToken' => true,
            'createdAt' => $integration->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $integration->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ]);
    }

    #[Route('/status', name: 'telegram_status', methods: ['GET'])]
    public function status(int $shopId): JsonResponse
    {
        $shop = $this->getShopOr404($shopId);
        $this->denyAccessUnlessGranted('SHOP_ACCESS', $shop);

        return $this->json($this->integrationService->getStatus($shop));
    }

    private function getShopOr404(int $shopId): Shop
    {
        $shop = $this->shopRepository->find($shopId);
        if ($shop === null) {
            throw $this->createNotFoundException('Shop not found');
        }

        return $shop;
    }
}
