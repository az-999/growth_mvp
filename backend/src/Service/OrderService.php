<?php

namespace App\Service;

use App\Dto\CreateOrderRequest;
use App\Entity\Order;
use App\Entity\Shop;
use App\Entity\TelegramSendLog;
use App\Repository\TelegramIntegrationRepository;
use App\Repository\TelegramSendLogRepository;
use App\Logger\TelegramLogger;
use App\Telegram\TelegramClient;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TelegramIntegrationRepository $integrationRepository,
        private readonly TelegramSendLogRepository $sendLogRepository,
        private readonly TelegramClient $telegramClient,
        private readonly TokenEncryptor $tokenEncryptor,
        private readonly TelegramLogger $telegramLogger,
    ) {
    }

    /**
     * @return array{order: Order, sendStatus: string}
     */
    public function createAndNotify(Shop $shop, CreateOrderRequest $dto): array
    {
        $order = new Order();
        $order->setShop($shop);
        $order->setNumber($dto->number);
        $order->setTotal((string) $dto->total);
        $order->setCustomerName($dto->customerName);

        $this->em->persist($order);
        $this->em->flush();

        $sendStatus = $this->notifyForOrder($order);

        return ['order' => $order, 'sendStatus' => $sendStatus];
    }

    public function notifyForOrder(Order $order): string
    {
        $shop = $order->getShop();
        $integration = $this->integrationRepository->findByShop($shop);

        if ($integration === null) {
            $this->telegramLogger->info('notify_skipped', [
                'shopId' => $shop->getId(),
                'orderId' => $order->getId(),
                'reason' => 'no_telegram_integration',
            ]);

            return 'skipped';
        }

        if (!$integration->isEnabled()) {
            $this->telegramLogger->info('notify_skipped', [
                'shopId' => $shop->getId(),
                'orderId' => $order->getId(),
                'reason' => 'integration_disabled',
            ]);

            return 'skipped';
        }

        $existing = $this->sendLogRepository->findByShopAndOrder($shop, $order);
        if ($existing !== null) {
            $status = strtolower($existing->getStatus()) === 'sent' ? 'sent' : 'failed';
            $this->telegramLogger->info('notify_idempotent', [
                'shopId' => $shop->getId(),
                'orderId' => $order->getId(),
                'existingStatus' => $existing->getStatus(),
            ]);

            return $status;
        }

        $message = $this->buildMessage($order);

        $this->telegramLogger->info('notify_attempt', [
            'shopId' => $shop->getId(),
            'orderId' => $order->getId(),
            'orderNumber' => $order->getNumber(),
            'chatId' => TelegramLogger::maskChatId($integration->getChatId()),
            'messagePreview' => mb_substr($message, 0, 120),
        ]);

        try {
            $token = $this->tokenEncryptor->decrypt($integration->getBotTokenEncrypted());
            $this->telegramClient->sendMessage($token, $integration->getChatId(), $message);
            $this->persistLog($shop, $order, $message, TelegramSendLog::STATUS_SENT, null);

            $this->telegramLogger->info('notify_success', [
                'shopId' => $shop->getId(),
                'orderId' => $order->getId(),
            ]);

            return 'sent';
        } catch (\Throwable $e) {
            $this->telegramLogger->error('notify_failed', [
                'shopId' => $shop->getId(),
                'orderId' => $order->getId(),
                'error' => $e->getMessage(),
            ]);
            $this->persistLog($shop, $order, $message, TelegramSendLog::STATUS_FAILED, $e->getMessage());

            return 'failed';
        }
    }

    private function buildMessage(Order $order): string
    {
        return sprintf(
            'Новый заказ %s на сумму %s ₽, клиент %s',
            $order->getNumber(),
            $order->getTotal(),
            $order->getCustomerName()
        );
    }

    private function persistLog(
        Shop $shop,
        Order $order,
        string $message,
        string $status,
        ?string $error,
    ): void {
        $log = new TelegramSendLog();
        $log->setShop($shop);
        $log->setOrder($order);
        $log->setMessage($message);
        $log->setStatus($status);
        $log->setError($error);

        $this->em->persist($log);
        $this->em->flush();
    }
}
