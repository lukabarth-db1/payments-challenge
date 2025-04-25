<?php

declare(strict_types=1);

namespace App\Service\Payments\RequestPaymentService;

use App\Service\Payments\CancelPaymentService;

class CancelPaymentHandler
{
    public function __construct(private readonly CancelPaymentService $cancelPaymentService) {}

    public function __invoke(int $paymentId): void
    {
        $this->cancelPaymentService->execute($paymentId);
    }
}
