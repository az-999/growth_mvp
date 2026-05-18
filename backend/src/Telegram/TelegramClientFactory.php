<?php

namespace App\Telegram;

use App\Logger\TelegramLogger;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramClientFactory
{
    public function __construct(
        private readonly bool $telegramMock,
        private readonly HttpClientInterface $httpClient,
        private readonly TelegramLogger $logger,
    ) {
    }

    public function create(): TelegramClient
    {
        if ($this->telegramMock) {
            $this->logger->info('client_mode', ['mode' => 'mock', 'env' => 'TELEGRAM_MOCK=true']);

            return new MockTelegramClient($this->logger);
        }

        $this->logger->info('client_mode', ['mode' => 'http', 'env' => 'TELEGRAM_MOCK=false']);

        return new HttpTelegramClient($this->httpClient, $this->logger);
    }
}
