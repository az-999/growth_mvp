<?php

namespace App\Logger;

/**
 * Пишет в stderr — видно в: docker compose logs -f backend_php
 */
final class TelegramLogger
{
    public function info(string $event, array $context = []): void
    {
        $this->write('INFO', $event, $context);
    }

    public function error(string $event, array $context = []): void
    {
        $this->write('ERROR', $event, $context);
    }

    public static function maskToken(string $botToken): string
    {
        if ($botToken === '') {
            return '(empty)';
        }
        if (strlen($botToken) <= 8) {
            return '****';
        }

        return substr($botToken, 0, 4).'…'.substr($botToken, -4);
    }

    public static function maskChatId(string $chatId): string
    {
        if (strlen($chatId) <= 4) {
            return '****';
        }

        return str_repeat('*', strlen($chatId) - 4).substr($chatId, -4);
    }

    private function write(string $level, string $event, array $context): void
    {
        $payload = $context !== [] ? ' '.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) : '';
        error_log(sprintf('[telegram][%s] %s%s', $level, $event, $payload));
    }
}
