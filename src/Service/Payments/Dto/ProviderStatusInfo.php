<?php

declare(strict_types=1);

namespace App\Service\Payments\Dto;

class ProviderStatusInfo
{
    public function __construct(
        public readonly int $paymentId,
        public readonly string $provider,
        public readonly string $operation,
    ) {}
}
