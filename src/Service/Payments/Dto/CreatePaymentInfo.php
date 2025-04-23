<?php

declare(strict_types=1);

namespace App\Service\Payments\Dto;

class CreatePaymentInfo
{
    public function __construct(
        public readonly float $amount,
        public readonly string $type,
        public readonly string $country,
        public readonly int $customerId,
    ) {}
}
