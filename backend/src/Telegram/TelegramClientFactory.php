<?php

namespace App\Telegram;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramClientFactory
{
    public function __construct(
        private readonly bool $telegramMock,
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function create(): TelegramClient
    {
        if ($this->telegramMock) {
            return new MockTelegramClient();
        }

        return new HttpTelegramClient($this->httpClient);
    }
}
