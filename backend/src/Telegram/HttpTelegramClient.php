<?php

namespace App\Telegram;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpTelegramClient implements TelegramClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function sendMessage(string $botToken, string $chatId, string $text): void
    {
        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', $botToken);
        $response = $this->httpClient->request('POST', $url, [
            'json' => [
                'chat_id' => $chatId,
                'text' => $text,
            ],
        ]);

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException(sprintf('Telegram API error: HTTP %d', $status));
        }

        $data = $response->toArray(false);
        if (!($data['ok'] ?? false)) {
            throw new \RuntimeException($data['description'] ?? 'Telegram API error');
        }
    }
}
