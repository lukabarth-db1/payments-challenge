<?php

namespace App\Gateway\Contracts\Dto;

class GatewayPaymentInfo
{
    public function __construct(
        public readonly float $amount,
        public readonly string $type,
        public readonly string $country,
    ) {}
}
