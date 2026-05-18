<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TelegramConnectRequest
{
    #[Assert\NotBlank(message: 'botToken must not be empty')]
    public string $botToken = '';

    #[Assert\NotBlank(message: 'chatId must not be empty')]
    public string $chatId = '';

    public bool $enabled = true;
}
