<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Domain\Payment;
use App\Gateway\Contracts\PaymentGatewayInterface;
use App\Service\Customers\CreateCustomerService;
use App\Service\Providers\ProviderLogService;

class RequestPaymentService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly ProviderLogService $logService,
    ) {}

    public function handle(array $requestBody): Payment
    {
        $gatewayResponse = $this->gateway->create($requestBody);

        $createCustomerService = new CreateCustomerService($requestBody);
        $customerId = $createCustomerService->getOrCreateCustomerId();

        $createPaymentService = new CreatePaymentService($requestBody, $customerId);

        $payment = $createPaymentService->execute();

        $this->logService->log(
            $gatewayResponse['gateway'],
            'create',
            $payment->id,
        );

        return $payment;
    }
}
