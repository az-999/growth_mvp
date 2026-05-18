<?php

namespace App\Tests\Service;

use App\Dto\CreateOrderRequest;
use App\Entity\Order;
use App\Repository\TelegramSendLogRepository;
use App\Service\OrderService;

class OrderNotificationIdempotencyTest extends OrderNotificationTestCase
{
    public function testNoDuplicateSendOrLog(): void
    {
        $this->createIntegration(true);
        $service = static::getContainer()->get(OrderService::class);

        $dto = new CreateOrderRequest();
        $dto->number = 'A-2001';
        $dto->total = 1500;
        $dto->customerName = 'Иван';

        $result = $service->createAndNotify($this->shop, $dto);
        $order = $result['order'];

        $this->assertSame(1, $this->spy->getCallCount());

        $status2 = $service->notifyForOrder($order);
        $this->assertSame('sent', $status2);
        $this->assertSame(1, $this->spy->getCallCount());

        $logRepo = static::getContainer()->get(TelegramSendLogRepository::class);
        $logs = $logRepo->findBy(['shop' => $this->shop, 'order' => $order]);
        $this->assertCount(1, $logs);
    }
}
