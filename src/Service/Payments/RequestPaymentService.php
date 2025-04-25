<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Domain\Payment;
use App\Gateway\Contracts\Dto\GatewayPaymentInfo;
use App\Gateway\Contracts\PaymentGateway;
use App\Gateway\GatewayOperation;
use App\Service\Customers\CreateCustomerService;
use App\Service\Dto\RequestPaymentData;
use App\Service\Payments\Dto\CreatePaymentInfo;
use App\Service\Providers\ProviderLogService;

class RequestPaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly ProviderLogService $logService,
        private readonly CreateCustomerService $createCustomerService,
        private readonly CreatePaymentService $createPaymentService,
    ) {}

    public function handle(RequestPaymentData $data): Payment
    {
        $gatewayResponse = $this->gateway->create(
            new GatewayPaymentInfo(
                amount: $data->paymentAmount,
                type: $data->paymentType,
                country: $data->paymentCountry,
            )
        );

        $customerId = $this->createCustomerService->getOrCreateCustomerId($data->customer);

        $createPaymentInfo = new CreatePaymentInfo(
            amount: $data->paymentAmount,
            type: $data->paymentType,
            country: $data->paymentCountry,
            customerId: $customerId
        );

        $payment = $this->createPaymentService->execute($createPaymentInfo);

        $this->logService->log(
            $gatewayResponse->gateway,
            GatewayOperation::CREATE->value,
            $payment->id,
        );

        return $payment;
    }
}
