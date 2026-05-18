<?php

namespace App\Tests\Service;

use App\Dto\CreateOrderRequest;
use App\Entity\TelegramSendLog;
use App\Repository\OrderRepository;
use App\Repository\TelegramSendLogRepository;
use App\Service\OrderService;

class OrderNotificationFailedTest extends OrderNotificationTestCase
{
    public function testFailedLogButOrderCreated(): void
    {
        $this->createIntegration(true);
        $this->spy->setThrowOnSend(true);

        $service = static::getContainer()->get(OrderService::class);
        $dto = new CreateOrderRequest();
        $dto->number = 'A-3001';
        $dto->total = 990;
        $dto->customerName = 'Тест';

        $result = $service->createAndNotify($this->shop, $dto);

        $this->assertSame('failed', $result['sendStatus']);
        $this->assertNotNull($result['order']->getId());

        $orderRepo = static::getContainer()->get(OrderRepository::class);
        $this->assertNotNull($orderRepo->find($result['order']->getId()));

        $logRepo = static::getContainer()->get(TelegramSendLogRepository::class);
        $log = $logRepo->findByShopAndOrder($this->shop, $result['order']);

        $this->assertNotNull($log);
        $this->assertSame(TelegramSendLog::STATUS_FAILED, $log->getStatus());
        $this->assertNotEmpty($log->getError());
    }
}
