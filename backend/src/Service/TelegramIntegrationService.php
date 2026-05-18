<?php

namespace App\Service;

use App\Dto\TelegramConnectRequest;
use App\Entity\Shop;
use App\Entity\TelegramIntegration;
use App\Repository\TelegramIntegrationRepository;
use App\Repository\TelegramSendLogRepository;
use Doctrine\ORM\EntityManagerInterface;

class TelegramIntegrationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TelegramIntegrationRepository $integrationRepository,
        private readonly TelegramSendLogRepository $sendLogRepository,
        private readonly TokenEncryptor $tokenEncryptor,
    ) {
    }

    public function connect(Shop $shop, TelegramConnectRequest $dto): TelegramIntegration
    {
        $integration = $this->integrationRepository->findByShop($shop);

        if ($integration === null) {
            if ($dto->botToken === '') {
                throw new \InvalidArgumentException('botToken is required for first connect');
            }
            $integration = new TelegramIntegration();
            $integration->setShop($shop);
            $this->em->persist($integration);
        }

        if ($dto->botToken !== '') {
            $integration->setBotTokenEncrypted($this->tokenEncryptor->encrypt($dto->botToken));
        }
        $integration->setChatId($dto->chatId);
        $integration->setEnabled($dto->enabled);
        $integration->touch();

        $this->em->flush();

        return $integration;
    }

    /**
     * @return array<string, mixed>
     */
    public function getStatus(Shop $shop): array
    {
        $integration = $this->integrationRepository->findByShop($shop);
        $since = new \DateTimeImmutable('-7 days');

        if ($integration === null) {
            return [
                'enabled' => false,
                'chatId' => null,
                'lastSentAt' => null,
                'sentCount' => 0,
                'failedCount' => 0,
            ];
        }

        return [
            'enabled' => $integration->isEnabled(),
            'chatId' => $this->maskChatId($integration->getChatId()),
            'lastSentAt' => $this->sendLogRepository->findLastSentAt($shop)?->format(\DateTimeInterface::ATOM),
            'sentCount' => $this->sendLogRepository->countByShopAndStatusSince(
                $shop,
                \App\Entity\TelegramSendLog::STATUS_SENT,
                $since
            ),
            'failedCount' => $this->sendLogRepository->countByShopAndStatusSince(
                $shop,
                \App\Entity\TelegramSendLog::STATUS_FAILED,
                $since
            ),
        ];
    }

    private function maskChatId(string $chatId): string
    {
        $len = strlen($chatId);
        if ($len <= 4) {
            return '****';
        }

        return str_repeat('*', $len - 4).substr($chatId, -4);
    }
}
