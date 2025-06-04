<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Domain\Payment;
use App\Gateway\Contracts\Dto\GatewayPaymentInfo;
use App\Gateway\Contracts\GatewayFactory;
use App\Service\Customers\CreateCustomerService;
use App\Service\Dto\RequestPaymentData;
use App\Service\Payments\Dto\CreatePaymentInfo;
use App\Service\Providers\ProviderLogService;

class RequestPaymentService
{
    public function __construct(
        private readonly GatewayFactory $gatewayFactory,
        private readonly ProviderLogService $logService,
        private readonly CreateCustomerService $createCustomerService,
        private readonly CreatePaymentService $createPaymentService,
    ) {}

    public function handle(RequestPaymentData $data, string $gatewayStatus): Payment
    {
        $gateway = $this->gatewayFactory->gatewayHandler($data->paymentCountry);

        $gatewayResponse = $gateway->create(
            new GatewayPaymentInfo(
                amount: $data->paymentAmount,
                type: $data->paymentType,
                country: $data->paymentCountry,
            ),
            $gatewayStatus
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
            $gatewayStatus,
            $payment->id,
        );

        return $payment;
    }
}
