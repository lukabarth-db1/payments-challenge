<?php

declare(strict_types=1);

namespace App\Gateway;

use App\Exceptions\PaymentStatusException;
use App\Gateway\Contracts\Dto\GatewayPaymentInfo;
use App\Gateway\Contracts\PaymentGateway;
use Exception;
use App\Gateway\Contracts\Dto\GatewayResponse;
use App\Helpers\PaymentStatus;

class PagueFacil implements PaymentGateway
{
    public function create(GatewayPaymentInfo $data, string $gatewayStatus): GatewayResponse
    {
        $country = $data->country;

        if ($country !== 'BR') {
            throw new Exception("PagueFacil only accepts payments from Brazil");
        }

        return new GatewayResponse(
            status: $gatewayStatus,
            gateway: 'PagueFacil',
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
