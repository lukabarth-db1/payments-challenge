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
    public function __construct(
        private readonly CreatePaymentInfo $paymentInfo,
    ) {}

    public function execute(): Payment
    {
        $this->persistPayment();

        return $this->getLastInsertedPayment();
    }

    private function getLastInsertedPayment(): Payment
    {
        $statement = new Statement("SELECT * FROM payments ORDER BY id DESC LIMIT 1");
        $statement->returningResults();

        $paymentRow = Database::execute($statement)->getRows()[0];

        return new Payment(
            id: $paymentRow['id'],
            amount: (string)$paymentRow['amount'],
            type: $paymentRow['type'],
            country: $paymentRow['country'],
            status: $paymentRow['status']
        );
    }

    private function persistPayment(): void
    {
        $statement = DatabaseOperation::table('payments')
            ->insert()
            ->data($this->mappingValuesPayments())
            ->build();

        Database::execute($statement);
    }

    private function mappingValuesPayments(): array
    {
        return [
            'amount' => $this->paymentInfo->amount,
            'type' => $this->paymentInfo->type,
            'country' => $this->paymentInfo->country,
            'status' => PaymentStatus::PENDING,
            'customer_id' => $this->paymentInfo->customerId,
        ];
    }
}
