<?php

declare(strict_types=1);

namespace App\Gateway\Contracts;

use App\Gateway\Contracts\Dto\GatewayPaymentInfo;
use App\Gateway\Contracts\Dto\GatewayResponse;

interface PaymentGateway
{
    public function create(GatewayPaymentInfo $data, string $gatewayStatus): GatewayResponse;
    public function confirm(string $status): void;
    public function cancel(string $status): void;
    public function refund(string $status): void;
}
