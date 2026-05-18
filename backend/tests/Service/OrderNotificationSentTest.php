<?php

namespace App\Tests\Service;

use App\Dto\CreateOrderRequest;
use App\Entity\TelegramSendLog;
use App\Repository\TelegramSendLogRepository;
use App\Service\OrderService;

class OrderNotificationSentTest extends OrderNotificationTestCase
{
    public function testSentOnOrderWithEnabledIntegration(): void
    {
        $this->createIntegration(true);
        $service = static::getContainer()->get(OrderService::class);

        $dto = new CreateOrderRequest();
        $dto->number = 'A-1005';
        $dto->total = 2490;
        $dto->customerName = 'Анна';

        $result = $service->createAndNotify($this->shop, $dto);

        $this->assertSame('sent', $result['sendStatus']);
        $this->assertSame(1, $this->spy->getCallCount());

        $logRepo = static::getContainer()->get(TelegramSendLogRepository::class);
        $log = $logRepo->findByShopAndOrder($this->shop, $result['order']);

        $this->assertNotNull($log);
        $this->assertSame(TelegramSendLog::STATUS_SENT, $log->getStatus());
        $this->assertNull($log->getError());
        $this->assertStringContainsString('A-1005', $log->getMessage());
    }
}
