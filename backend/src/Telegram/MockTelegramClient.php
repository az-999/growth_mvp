<?php

namespace App\Telegram;

class MockTelegramClient implements TelegramClient
{
    public function sendMessage(string $botToken, string $chatId, string $text): void
    {
        // Mock: no-op success
    }
}
