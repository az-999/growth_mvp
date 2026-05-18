<?php

namespace App\Tests\Service;

use App\Entity\Shop;
use App\Entity\TelegramIntegration;
use App\Service\TokenEncryptor;
use App\Tests\Double\SpyTelegramClient;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class OrderNotificationTestCase extends KernelTestCase
{
    protected SpyTelegramClient $spy;
    protected Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel(['environment' => 'test']);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $meta = $em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($em);
        $tool->dropSchema($meta);
        $tool->createSchema($meta);

        $this->spy = static::getContainer()->get(SpyTelegramClient::class);
        $this->spy->reset();

        $this->shop = new Shop('Test Shop');
        $em->persist($this->shop);
        $em->flush();
    }

    protected function createIntegration(bool $enabled = true): void
    {
        $encryptor = static::getContainer()->get(TokenEncryptor::class);
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $integration = new TelegramIntegration();
        $integration->setShop($this->shop);
        $integration->setBotTokenEncrypted($encryptor->encrypt('123456:TEST-BOT-TOKEN'));
        $integration->setChatId('987654321');
        $integration->setEnabled($enabled);
        $em->persist($integration);
        $em->flush();
    }
}
