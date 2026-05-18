<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TelegramConnectRequest
{
    public string $botToken = '';

    #[Assert\NotBlank(message: 'chatId must not be empty')]
    public string $chatId = '';

    public bool $enabled = true;
}
