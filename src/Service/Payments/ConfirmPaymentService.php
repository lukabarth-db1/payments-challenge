<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Exceptions\PaymentStatusException;
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
            throw new PaymentStatusException($currentStatus);
        }

        $this->paymentStatusService->updatePaymentStatus($paymentId, $confirmedStatus);
    }
}
