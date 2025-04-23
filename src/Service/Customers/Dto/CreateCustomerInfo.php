<?php

namespace App\Service\Customers\Dto;

class CreateCustomerInfo
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $document,
    ) {}
}
