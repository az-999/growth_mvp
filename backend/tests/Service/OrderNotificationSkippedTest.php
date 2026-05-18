<?php

namespace App\Tests\Service;

use App\Dto\CreateOrderRequest;
use App\Repository\TelegramSendLogRepository;
use App\Service\OrderService;

class OrderNotificationSkippedTest extends OrderNotificationTestCase
{
    public function testSkippedWhenIntegrationDisabled(): void
    {
        $this->createIntegration(false);
        $service = static::getContainer()->get(OrderService::class);

        $dto = new CreateOrderRequest();
        $dto->number = 'A-4001';
        $dto->total = 1200;
        $dto->customerName = 'Гость';
        $dto->count = 1;
        $dto->productId = 'a1';

        $result = $service->createAndNotify($this->shop, $dto);

        $this->assertSame('skipped', $result['sendStatus']);
        $this->assertSame(0, $this->spy->getCallCount());

        $logRepo = static::getContainer()->get(TelegramSendLogRepository::class);
        $logs = $logRepo->findBy(['shop' => $this->shop]);
        $this->assertCount(0, $logs);
    }
}
