<?php

declare(strict_types=1);

namespace App\Service\Payments\Repository;

use Phractico\Core\Facades\Database;
use Phractico\Core\Infrastructure\Database\Query\Statement;

class ProviderRepository
{
    public function FindById(int $paymentId): string
    {
        $statement = new Statement("SELECT provider FROM provider_logs WHERE payment_id = {$paymentId}");
        $statement->returningResults();

        $result = Database::execute($statement)->getRows()[0];

        return $result['provider'];
    }
}
