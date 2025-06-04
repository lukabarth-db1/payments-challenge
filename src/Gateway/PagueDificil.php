<?php

declare(strict_types=1);

namespace App\Gateway;

use App\Exceptions\GatewayException;
use App\Exceptions\PaymentStatusException;
use App\Gateway\Contracts\Dto\GatewayPaymentInfo;
use App\Gateway\Contracts\PaymentGateway;
use App\Gateway\Contracts\Dto\GatewayResponse;
use App\Helpers\PaymentStatus;

class PagueDificil implements PaymentGateway
{
    public function create(GatewayPaymentInfo $data, string $gatewayStatus): GatewayResponse
    {
        $country = $data->country;

        if ($country !== 'AR') {
            throw new GatewayException("PagueDificil only accepts payments from Argentina");
        }

        return new GatewayResponse(
            status: $gatewayStatus,
            gateway: 'PagueDificil',
        );
    }

    public function confirm(string $status): void
    {
        if ($status !== PaymentStatus::PENDING->value) {
            throw new PaymentStatusException($status);
        }
    }

    public function cancel(string $status): void
    {
        if ($status !== PaymentStatus::PENDING->value) {
            throw new PaymentStatusException($status);
        }
    }

    public function refund(string $status): void
    {
        if ($status !== PaymentStatus::CONFIRMED->value) {
            throw new PaymentStatusException($status);
        }
    }
}
