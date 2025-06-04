<?php

declare(strict_types=1);

namespace App\Service\Payments\RequestPaymentService;

use App\Gateway\GatewayOperation;
use App\Service\Payments\CancelPaymentService;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Payments\Repository\ProviderRepository;

class CancelPaymentHandler
{
    public function __construct(
        private readonly CancelPaymentService $cancelPaymentService,
        private readonly ProviderRepository $providerRepository,
    ) {}

    public function __invoke(int $paymentId): void
    {
        $provider = $this->providerRepository->FindById($paymentId);

        $cancelPaymentInfo = new ProviderStatusInfo(
            paymentId: $paymentId,
            provider: (string)$provider,
            operation: GatewayOperation::CANCEL->value,
        );

        $this->cancelPaymentService->execute($cancelPaymentInfo);
    }
}
