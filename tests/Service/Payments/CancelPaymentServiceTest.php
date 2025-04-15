<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Database\Connection\SQLiteAdapter;
use PHPUnit\Framework\TestCase;
use Phractico\Core\Facades\Database;
use Phractico\Core\Infrastructure\Database\DatabaseConnection;
use Phractico\Core\Infrastructure\Database\Query\Statement;

class CancelPaymentServiceTest extends TestCase
{
    /**
     * @before
     */
    public function init(): void
    {
        $connection = new SQLiteAdapter(__DIR__ . '/../../../database-test.sqlite');
        DatabaseConnection::setConnection($connection);
    }

    public function testExecute_ShouldCancelPaymentInDatabase(): void
    {
        // arrange - prepare test
        $requestBody = [
            'payment' => [
                'type' => 'PHPUnitCancel',
                'country' => 'BR',
                'amount' => 2550.98
            ],
            'customer' => [
                'name' => 'PHP Cancel',
                'email' => 'phpunitcancel@email.com',
                'document' => '45889645896'
            ]
        ];

        $createPaymentService = new CreatePaymentService($requestBody);
        $payment = $createPaymentService->execute();
        $paymentId = $payment['id'];

        // act - run test
        $cancelPaymentService = new CancelPaymentService();
        $cancelPaymentService->execute($paymentId);

        // assert - check assert
        $lastInsertedPayment = $this->retrieveLastInsertedPayment();

        $this->assertEquals('canceled', $lastInsertedPayment['status']);
    }

    private function retrieveLastInsertedPayment(): array
    {
        $statement = new Statement("SELECT * FROM payments ORDER BY id DESC LIMIT 1");
        $statement->returningResults();

        return Database::execute($statement)->getRows()[0];
    }
}
