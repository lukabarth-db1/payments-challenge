<?php

declare(strict_types=1);

namespace App\Service\Payments;

use DomainException;

class RefundPaymentService
{
    public function execute(int $paymentId): void
    {
        $refundStatus = 'refund';
        $paymentStatusService = new PaymentStatusService();

        $currentStatus = $paymentStatusService->getStatus($paymentId);

        if ($currentStatus !== 'confirmed') {
            throw new DomainException("payment id {$paymentId} cannot be refunded");
        }

        $paymentStatusService->updatePaymentStatus($paymentId, $refundStatus);
    }
}
