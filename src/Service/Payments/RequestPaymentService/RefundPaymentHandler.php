<?php

declare(strict_types=1);

namespace App\Service\Payments\RequestPaymentService;

use App\Gateway\GatewayOperation;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Payments\RefundPaymentService;

class RefundPaymentHandler
{
    public function __construct(private readonly RefundPaymentService $refundPaymentService) {}

    public function __invoke(int $paymentId): void
    {
        $refundPaymentInfo = new ProviderStatusInfo(
            paymentId: $paymentId,
            provider: 'PagueFacil',
            operation: GatewayOperation::REFUND->value,
        );

        $this->refundPaymentService->execute($refundPaymentInfo);
    }
}
