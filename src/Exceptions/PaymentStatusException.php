<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

class PaymentStatusException extends DomainException
{
    public function __construct(string $status)
    {
        parent::__construct("Invalid payment status: " . $status);
    }
}
