<?php

namespace App\Telegram;

interface TelegramClient
{
    public function sendMessage(string $botToken, string $chatId, string $text): void;
}
