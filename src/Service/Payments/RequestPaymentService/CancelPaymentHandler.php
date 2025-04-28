<?php

declare(strict_types=1);

namespace App\Service\Payments\RequestPaymentService;

use App\Gateway\GatewayOperation;
use App\Service\Payments\CancelPaymentService;
use App\Service\Payments\Dto\ProviderStatusInfo;

class CancelPaymentHandler
{
    public function __construct(private readonly CancelPaymentService $cancelPaymentService) {}

    public function __invoke(int $paymentId): void
    {
        $cancelPaymentInfo = new ProviderStatusInfo(
            paymentId: $paymentId,
            provider: 'PagueFacil',
            operation: GatewayOperation::CANCEL->value,
        );

        $this->cancelPaymentService->execute($cancelPaymentInfo);
    }
}
