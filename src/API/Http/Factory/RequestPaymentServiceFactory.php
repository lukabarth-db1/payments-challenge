<?php

declare(strict_types=1);

namespace App\API\Http\Factory;

use App\Gateway\Contracts\PaymentGatewayInterface;
use App\Service\Customers\CreateCustomerService;
use App\Service\Payments\CreatePaymentService;
use App\Service\Payments\RequestPaymentService;
use App\Service\Providers\ProviderLogService;

class RequestPaymentServiceFactory
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly ProviderLogService $logService,
        private readonly CreateCustomerService $createCustomerService,
        private readonly CreatePaymentService $createPaymentService,
    ) {}

    public function create(): RequestPaymentService
    {
        return new RequestPaymentService(
            gateway: $this->gateway,
            logService: $this->logService,
            createCustomerService: $this->createCustomerService,
            createPaymentService: $this->createPaymentService,
        );
    }
}
