<?php

declare(strict_types=1);

namespace App\Gateway\Contracts;

use App\Exceptions\GatewayException;
use App\Gateway\PagueDificil;
use App\Gateway\PagueFacil;

class GatewayFactory
{
    public function gatewayHandler(string $country): PaymentGateway
    {
        return match (strtolower($country)) {
            'br' => new PagueFacil(),
            'ar' => new PagueDificil(),
            default => throw new GatewayException($country)
        };
    }
}
