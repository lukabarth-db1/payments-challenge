<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Helpers\PaymentStatus;
use DomainException;

class RefundPaymentService
{
    public function __construct(private PaymentStatusService $paymentStatusService) {}

    public function execute(int $paymentId): void
    {
        $refundStatus = 'refund';

        $currentStatus = $this->paymentStatusService->getStatus($paymentId);

        if ($currentStatus !== PaymentStatus::CONFIRMED) {
            throw new DomainException("payment id {$paymentId} cannot be refunded");
        }

        $this->paymentStatusService->updatePaymentStatus($paymentId, $refundStatus);
    }
}
