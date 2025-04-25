<?php

declare(strict_types=1);

namespace App\Service\Payments\RequestPaymentService;

use App\Service\Payments\RefundPaymentService;

class RefundPaymentHandler
{
    public function __construct(private readonly RefundPaymentService $refundPaymentService) {}

    public function __invoke(int $paymentId): void
    {
        $this->refundPaymentService->execute($paymentId);
    }
}
