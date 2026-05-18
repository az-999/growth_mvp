<?php

namespace App\Tests\Double;

use App\Telegram\TelegramClient;

class SpyTelegramClient implements TelegramClient
{
    private int $callCount = 0;
    private bool $throwOnSend = false;
    /** @var list<array{token: string, chatId: string, text: string}> */
    private array $messages = [];

    public function sendMessage(string $botToken, string $chatId, string $text): void
    {
        ++$this->callCount;
        $this->messages[] = ['token' => $botToken, 'chatId' => $chatId, 'text' => $text];

        if ($this->throwOnSend) {
            throw new \RuntimeException('Telegram API error (test)');
        }
    }

    public function getCallCount(): int
    {
        return $this->callCount;
    }

    public function setThrowOnSend(bool $throw): void
    {
        $this->throwOnSend = $throw;
    }

    public function reset(): void
    {
        $this->callCount = 0;
        $this->throwOnSend = false;
        $this->messages = [];
    }
}
