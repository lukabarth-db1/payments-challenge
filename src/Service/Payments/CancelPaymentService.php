<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Helpers\PaymentStatus;
use DomainException;

class CancelPaymentService
{
    public function __construct(private PaymentStatusService $paymentStatusService) {}

    public function execute(int $paymentId): void
    {
        $canceledStatus = 'canceled';

        $status = $this->paymentStatusService->getStatus($paymentId);

        if ($status !== PaymentStatus::PENDING) {
            throw new DomainException("payment id {$paymentId} cannot be canceled");
        }

        $this->paymentStatusService->updatePaymentStatus($paymentId, $canceledStatus);
    }
}
