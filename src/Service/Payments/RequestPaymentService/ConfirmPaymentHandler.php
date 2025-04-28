<?php

declare(strict_types=1);

namespace App\Service\Payments\RequestPaymentService;

use App\Gateway\GatewayOperation;
use App\Service\Payments\ConfirmPaymentService;
use App\Service\Payments\Dto\ProviderStatusInfo;

class ConfirmPaymentHandler
{
    public function __construct(private readonly ConfirmPaymentService $confirmPaymentService) {}

    public function __invoke(int $paymentId): void
    {
        $confirmPaymentInfo = new ProviderStatusInfo(
            paymentId: $paymentId,
            provider: 'PagueFacil',
            operation: GatewayOperation::CONFIRM->value,
        );

        $this->confirmPaymentService->execute($confirmPaymentInfo);
    }
}
