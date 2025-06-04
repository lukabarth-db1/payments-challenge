<?php

declare(strict_types=1);

namespace App\Service\Payments\RequestPaymentService;

use App\Gateway\GatewayOperation;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Payments\RefundPaymentService;
use App\Service\Payments\Repository\ProviderRepository;

class RefundPaymentHandler
{
    public function __construct(
        private readonly RefundPaymentService $refundPaymentService,
        private readonly ProviderRepository $providerRepository,
    ) {}

    public function __invoke(int $paymentId): void
    {
        $provider = $this->providerRepository->FindById($paymentId);

        $refundPaymentInfo = new ProviderStatusInfo(
            paymentId: $paymentId,
            provider: (string)$provider,
            operation: GatewayOperation::REFUND->value,
        );

        $this->refundPaymentService->execute($refundPaymentInfo);
    }
}
