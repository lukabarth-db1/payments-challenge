<?php

declare(strict_types=1);

namespace App\Domain;

class Customer
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $document,
    ) {}
}
