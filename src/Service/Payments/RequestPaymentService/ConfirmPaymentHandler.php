<?php

declare(strict_types=1);

namespace App\Service\Payments\RequestPaymentService;

use App\Gateway\GatewayOperation;
use App\Service\Payments\ConfirmPaymentService;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Payments\Repository\ProviderRepository;

class ConfirmPaymentHandler
{
    public function __construct(
        private readonly ConfirmPaymentService $confirmPaymentService,
        private readonly ProviderRepository $providerRepository,
    ) {}

    public function __invoke(int $paymentId): void
    {
        $provider = $this->providerRepository->FindById($paymentId);

        $confirmPaymentInfo = new ProviderStatusInfo(
            paymentId: $paymentId,
            provider: (string)$provider,
            operation: GatewayOperation::CONFIRM->value,
        );

        $this->confirmPaymentService->execute($confirmPaymentInfo);
    }
}
