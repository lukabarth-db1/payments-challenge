<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Domain\Payment;
use App\Helpers\PaymentStatus;
use Phractico\Core\Facades\Database;
use Phractico\Core\Facades\DatabaseOperation;
use Phractico\Core\Infrastructure\Database\Query\Statement;

class CreatePaymentService
{
    public function __construct(
        private readonly array $requestBody,
        private readonly int $customerId,
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
            'amount' => $this->requestBody['payment']['amount'],
            'type' => $this->requestBody['payment']['type'],
            'country' => $this->requestBody['payment']['country'],
            'status' => PaymentStatus::PENDING,
            'customer_id' => $this->customerId,
        ];
    }
}
