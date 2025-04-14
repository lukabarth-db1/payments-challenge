<?php

declare(strict_types=1);

namespace App\Service\Payments;

use Phractico\Core\Facades\Database;
use Phractico\Core\Facades\DatabaseOperation;
use Phractico\Core\Infrastructure\Database\Query\Grammar\Comparison;
use Phractico\Core\Infrastructure\Database\Query\Statement;

class PaymentStatusService
{
    public function getStatus(int $paymentId): string
    {
        $statement = new Statement("SELECT status FROM payments WHERE id = {$paymentId}");
        $statement->returningResults();

        $result = Database::execute($statement)->getRows()[0]['status'];

        return $result;
    }

    public function updatePaymentStatus(int $paymentId, string $newStatus): void
    {
        $statement = DatabaseOperation::table('payments')
            ->update()
            ->data(['status' => $newStatus])
            ->where('id', Comparison::EQUAL, $paymentId)
            ->build();
        Database::execute($statement);
    }
}
