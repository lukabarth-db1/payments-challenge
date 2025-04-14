<?php

declare(strict_types=1);

namespace App\Service\Payments;

use DomainException;

class CancelPaymentService
{
    public function execute(int $paymentId): void
    {
        $canceledStatus = 'canceled';
        $paymentStatusService = new PaymentStatusService();

        $currentStatus = $paymentStatusService->getStatus($paymentId);

        if ($currentStatus !== 'pending') {
            throw new DomainException("payment id {$paymentId} cannot be cancel");
        }

        $paymentStatusService->updatePaymentStatus($paymentId, $canceledStatus);
    }
}
