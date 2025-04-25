<?php

declare(strict_types=1);

namespace App\Service\Payments\RequestPaymentService;

use App\Service\Payments\ConfirmPaymentService;

class ConfirmPaymentHandler
{
    public function __construct(private readonly ConfirmPaymentService $confirmPaymentService) {}

    public function __invoke(int $paymentId): void
    {
        $this->confirmPaymentService->execute($paymentId);
    }
}
