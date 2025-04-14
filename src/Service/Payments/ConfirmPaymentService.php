<?php

declare(strict_types=1);

namespace App\Service\Payments;

use DomainException;

class ConfirmPaymentService
{
    public function execute(int $paymentId): void
    {
        $confirmedStatus = 'confirmed';
        $paymentStatusService = new PaymentStatusService();

        $currentStatus = $paymentStatusService->getStatus($paymentId);

        if ($currentStatus !== 'pending') {
            throw new DomainException("payment id {$paymentId} cannot be confirm");
        }

        $paymentStatusService->updatePaymentStatus($paymentId, $confirmedStatus);
    }
}
