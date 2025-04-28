<?php

declare(strict_types=1);

namespace App\Gateway;

use App\Gateway\Contracts\Dto\GatewayPaymentInfo;
use App\Gateway\Contracts\PaymentGateway;
use Exception;
use App\Gateway\Contracts\Dto\GatewayResponse;

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
}
