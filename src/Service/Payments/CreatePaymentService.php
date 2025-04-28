<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Domain\Payment;
use App\Helpers\PaymentStatus;
use App\Service\Payments\Dto\CreatePaymentInfo;
use Phractico\Core\Facades\Database;
use Phractico\Core\Facades\DatabaseOperation;
use Phractico\Core\Infrastructure\Database\Query\Statement;

class CreatePaymentService
{
    public function execute(CreatePaymentInfo $paymentInfo): Payment
    {
        $this->persistPayment($paymentInfo);

        return $this->getLastInsertedPayment();
    }

    private function getLastInsertedPayment(): Payment
    {
        $statement = new Statement("SELECT * FROM payments ORDER BY id DESC LIMIT 1");
        $statement->returningResults();

        $paymentRow = Database::execute($statement)->getRows()[0];

        return new Payment(
            id: $paymentRow['id'],
            amount: $paymentRow['amount'],
            type: $paymentRow['type'],
            country: $paymentRow['country'],
            status: $paymentRow['status']
        );
    }

    private function persistPayment(CreatePaymentInfo $paymentInfo): void
    {
        $statement = DatabaseOperation::table('payments')
            ->insert()
            ->data($this->mappingValuesPayments($paymentInfo))
            ->build();

        Database::execute($statement);
    }

    private function mappingValuesPayments(CreatePaymentInfo $paymentInfo): array
    {
        return [
            'amount' => $paymentInfo->amount,
            'type' => $paymentInfo->type,
            'country' => $paymentInfo->country,
            'status' => PaymentStatus::PENDING->value,
            'customer_id' => $paymentInfo->customerId,
        ];
    }
}
