<?php

namespace App\Telegram;

use App\Logger\TelegramLogger;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpTelegramClient implements TelegramClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly TelegramLogger $logger,
    ) {
    }

    public function sendMessage(string $botToken, string $chatId, string $text): void
    {
        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', $botToken);

        $this->logger->info('api_request', [
            'chatId' => TelegramLogger::maskChatId($chatId),
            'botToken' => TelegramLogger::maskToken($botToken),
            'textLength' => strlen($text),
            'textPreview' => mb_substr($text, 0, 120),
        ]);

        $response = $this->httpClient->request('POST', $url, [
            'json' => [
                'chat_id' => $chatId,
                'text' => $text,
            ],
        ]);

        $status = $response->getStatusCode();
        $rawBody = $response->getContent(false);

        try {
            $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $data = null;
        }

        if ($status < 200 || $status >= 300) {
            $this->logger->error('api_http_error', [
                'httpStatus' => $status,
                'body' => $rawBody,
            ]);
            throw new \RuntimeException(sprintf('Telegram API error: HTTP %d', $status));
        }

        if (!is_array($data) || !($data['ok'] ?? false)) {
            $this->logger->error('api_response_error', [
                'httpStatus' => $status,
                'response' => $data ?? $rawBody,
            ]);
            throw new \RuntimeException(is_array($data) ? ($data['description'] ?? 'Telegram API error') : 'Telegram API error');
        }

        $this->logger->info('api_success', [
            'httpStatus' => $status,
            'messageId' => $data['result']['message_id'] ?? null,
            'chatId' => TelegramLogger::maskChatId((string) ($data['result']['chat']['id'] ?? $chatId)),
            'response' => $data,
        ]);
    }
}
