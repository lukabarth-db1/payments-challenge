<?php

declare(strict_types=1);

namespace App\Service\Payments\RequestPaymentService;

use App\Domain\Payment;
use App\Service\Dto\RequestPaymentData;
use App\Service\Payments\RequestPaymentService;

class HandleRequestPayment
{
    public function __construct(private RequestPaymentService $requestPaymentService) {}

    public function __invoke(RequestPaymentData $data): Payment
    {
        return $this->requestPaymentService->handle($data);
    }
}
