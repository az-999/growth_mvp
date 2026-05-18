<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateOrderRequest
{
    #[Assert\NotBlank]
    public string $number = '';

    #[Assert\NotBlank]
    #[Assert\Positive]
    public float $total = 0;

    #[Assert\NotBlank]
    public string $customerName = '';
}
