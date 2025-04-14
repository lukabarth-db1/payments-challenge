<?php

namespace App\Service\Providers;

use Phractico\Core\Facades\Database;
use Phractico\Core\Facades\DatabaseOperation;

class ProviderLogService
{
    public function log(string $provider, string $operation, int $paymentId): void
    {
        $statement = DatabaseOperation::table('provider_logs')
            ->insert()
            ->data([
                'provider' => $provider,
                'operation' => $operation,
                'payment_id' => $paymentId,
            ])
            ->build();

        Database::execute($statement);
    }
}
