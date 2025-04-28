<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Helpers\PaymentStatus;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Providers\ProviderLogService;
use DomainException;

class CancelPaymentService
{
    public function __construct(
        private PaymentStatusService $paymentStatusService,
        private readonly ProviderLogService $logService,
    ) {}

    public function execute(ProviderStatusInfo $paymentInfo): void
    {
        $status = $this->paymentStatusService->getStatus($paymentInfo->paymentId);

        if ($status !== PaymentStatus::PENDING->value) {
            throw new DomainException("payment id {$paymentInfo->paymentId} cannot be canceled");
        }

        $this->paymentStatusService->updatePaymentStatus($paymentInfo->paymentId, PaymentStatus::CANCELED->value);

        $this->logService->log(
            $paymentInfo->provider,
            $paymentInfo->operation,
            $paymentInfo->paymentId,
        );
    }
}
