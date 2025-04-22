<?php

declare(strict_types=1);

namespace App\Domain;

class Payment
{
    public function __construct(
        public readonly int $id,
        public readonly string $amount,
        public readonly string $status,
        public readonly string $type,
        public readonly string $country
    ) {}
}
