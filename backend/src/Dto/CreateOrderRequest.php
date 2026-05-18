<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;
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

    #[Assert\Positive]
    public int $count = 1;

    #[Assert\NotBlank]
    #[SerializedName('product_id')]
    public string $productId = '';
}
