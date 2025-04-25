<?php

declare(strict_types=1);

namespace App\Service\Dto;

use App\Service\Customers\Dto\CreateCustomerInfo;

class RequestPaymentData
{
    public function __construct(
        public readonly float $paymentAmount,
        public readonly string $paymentType,
        public readonly string $paymentCountry,
        public readonly CreateCustomerInfo $customer,
    ) {}
}
