<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Domain\Payment;
use App\Gateway\Contracts\PaymentGatewayInterface;
use App\Service\Customers\CreateCustomerService;
use App\Service\Customers\Dto\CreateCustomerInfo;
use App\Service\Payments\Dto\CreatePaymentInfo;
use App\Service\Providers\ProviderLogService;
use Exception;

class RequestPaymentService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly ProviderLogService $logService,
        private readonly CreateCustomerService $createCustomerService,
        private readonly CreatePaymentService $createPaymentService,
    ) {}

    public function handle(array $requestBody): Payment
    {
        if (!array_key_exists('payment', $requestBody) || !array_key_exists('customer', $requestBody)) {
            throw new Exception('missing payment or customer');
        }

        $gatewayResponse = $this->gateway->create($requestBody);

        $customerInfo = new CreateCustomerInfo(
            $requestBody['customer']['name'],
            $requestBody['customer']['email'],
            $requestBody['customer']['document'],
        );

        $customerId = $this->createCustomerService->getOrCreateCustomerId($customerInfo);

        $createPaymentInfo = new CreatePaymentInfo(
            $requestBody['payment']['amount'],
            $requestBody['payment']['type'],
            $requestBody['payment']['country'],
            $customerId,
        );

        $createPaymentService = new CreatePaymentService($createPaymentInfo);
        $payment = $createPaymentService->execute();

        $this->logService->log(
            $gatewayResponse['gateway'],
            'create',
            $payment->id,
        );

        return $payment;
    }
}
