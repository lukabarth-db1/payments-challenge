<?php

declare(strict_types=1);

namespace App\Gateway\Contracts;

use App\Gateway\Contracts\Dto\GatewayPaymentInfo;
use App\Gateway\Contracts\Dto\GatewayResponse;

interface PaymentGateway
{
    public function create(GatewayPaymentInfo $data): GatewayResponse;
    // public function confirm(int $paymentId): void;
    // public function cancel(int $paymentId): void;
    // public function refund(int $paymentId): void;
}
