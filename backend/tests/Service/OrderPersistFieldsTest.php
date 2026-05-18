<?php

namespace App\Tests\Service;

use App\Dto\CreateOrderRequest;
use App\Entity\Order;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;

class OrderPersistFieldsTest extends OrderNotificationTestCase
{
    public function testCountAndProductIdPersistedInDatabase(): void
    {
        $service = static::getContainer()->get(OrderService::class);
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $dto = new CreateOrderRequest();
        $dto->number = 'A-9001';
        $dto->total = 5000;
        $dto->customerName = 'Тест';
        $dto->count = 3;
        $dto->productId = 's2';

        $result = $service->createAndNotify($this->shop, $dto);
        $orderId = $result['order']->getId();
        $em->clear();

        $reloaded = $em->find(Order::class, $orderId);
        $this->assertNotNull($reloaded);
        $this->assertSame(3, $reloaded->getCount());
        $this->assertSame('s2', $reloaded->getProductId());
    }
}
