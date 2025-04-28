<?php

declare(strict_types=1);

namespace App\Gateway\Contracts\Dto;

class GatewayResponse
{
    public function __construct(
        public readonly string $status,
        public readonly string $gateway
    ) {}
}
