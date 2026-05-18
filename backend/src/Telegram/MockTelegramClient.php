<?php

namespace App\Telegram;

use App\Logger\TelegramLogger;

class MockTelegramClient implements TelegramClient
{
    public function __construct(
        private readonly TelegramLogger $logger,
    ) {
    }

    public function sendMessage(string $botToken, string $chatId, string $text): void
    {
        $this->logger->info('mock_send_skipped', [
            'reason' => 'TELEGRAM_MOCK=true — реальный запрос в Telegram не выполняется',
            'chatId' => TelegramLogger::maskChatId($chatId),
            'botToken' => TelegramLogger::maskToken($botToken),
            'textLength' => strlen($text),
            'textPreview' => mb_substr($text, 0, 120),
        ]);
    }
}
