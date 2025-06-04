<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

class GatewayException extends DomainException
{
    public function __construct(string $gateway)
    {
        parent::__construct("No gateway available for country: " . $gateway);
    }
}
