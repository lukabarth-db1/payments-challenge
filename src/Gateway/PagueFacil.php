<?php

declare(strict_types=1);

namespace App\Gateway;

use App\Gateway\Contracts\PaymentGatewayInterface;
use Exception;

class PagueFacil implements PaymentGatewayInterface
{
    public function create(array $data): array
    {
        $country = $data['payment']['country'];

        if ($country !== 'BR') {
            throw new Exception("PagueFacil only accepts payments from Brazil");
        }

        return [
            'status' => 'pending',
            'gateway' => 'PagueFacil'
        ];
    }
}
