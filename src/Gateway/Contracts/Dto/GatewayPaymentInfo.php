<?php

namespace App\Gateway\Contracts\Dto;

use App\Gateway\GatewayOperation;

class GatewayPaymentRequestInfo
{
    public function __construct(
        public readonly string $provider,
        public readonly GatewayOperation $operation,
        public readonly string $country,
        public readonly int $paymentId,
    ) {}
}
