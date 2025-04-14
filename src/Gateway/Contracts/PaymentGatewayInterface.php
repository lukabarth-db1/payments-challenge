<?php

declare(strict_types=1);

namespace App\Gateway\Contracts;

interface PaymentGatewayInterface
{
    public function create(array $data): array;
    // public function confirm(int $paymentId): void;
    // public function cancel(int $paymentId): void;
    // public function refund(int $paymentId): void;
}
