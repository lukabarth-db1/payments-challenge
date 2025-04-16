<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Helpers\PaymentStatus;
use DomainException;

class ConfirmPaymentService
{
    public function __construct(private PaymentStatusService $paymentStatusService) {}

    public function execute(int $paymentId): void
    {
        $confirmedStatus = 'confirmed';

        $currentStatus = $this->paymentStatusService->getStatus($paymentId);

        if ($currentStatus !== PaymentStatus::PENDING) {
            throw new DomainException("payment id {$paymentId} cannot be confirmed");
        }

        $this->paymentStatusService->updatePaymentStatus($paymentId, $confirmedStatus);
    }
}
