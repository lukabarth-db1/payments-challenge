<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Exceptions\PaymentStatusException;
use App\Helpers\PaymentStatus;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Providers\ProviderLogService;

class ConfirmPaymentService
{
    public function __construct(
        private PaymentStatusService $paymentStatusService,
        private readonly ProviderLogService $logService,
    ) {}

    public function execute(ProviderStatusInfo $paymentInfo): void
    {
        $currentStatus = $this->paymentStatusService->getStatus($paymentInfo->paymentId);

        if ($currentStatus !== PaymentStatus::PENDING->value) {
            throw new PaymentStatusException($currentStatus);
        }

        $this->paymentStatusService->updatePaymentStatus($paymentInfo->paymentId, PaymentStatus::CONFIRMED->value);

        $this->logService->log(
            $paymentInfo->provider,
            $paymentInfo->operation,
            $paymentInfo->paymentId,
        );
    }
}
