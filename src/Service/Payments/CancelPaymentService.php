<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Gateway\Contracts\PaymentGateway;
use App\Helpers\PaymentStatus;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Providers\ProviderLogService;

class CancelPaymentService
{
    public function __construct(
        private PaymentGateway $gateway,
        private PaymentStatusService $paymentStatusService,
        private readonly ProviderLogService $logService,
    ) {}

    public function execute(ProviderStatusInfo $paymentInfo): void
    {
        $currentStatus = $this->paymentStatusService->getStatus($paymentInfo->paymentId);

        $this->gateway->cancel($currentStatus);

        $this->paymentStatusService->updatePaymentStatus($paymentInfo->paymentId, PaymentStatus::CANCELED->value);

        $this->logService->log(
            $paymentInfo->provider,
            $paymentInfo->operation,
            $paymentInfo->paymentId,
        );
    }
}
