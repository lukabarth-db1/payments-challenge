<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Database\Connection\SQLiteAdapter;
use PHPUnit\Framework\TestCase;
use Phractico\Core\Facades\Database;
use Phractico\Core\Infrastructure\Database\DatabaseConnection;
use Phractico\Core\Infrastructure\Database\Query\Statement;

class ConfirmPaymentServiceTest extends TestCase
{
    /**
     * @before
     */
    public function init(): void
    {
        $connection = new SQLiteAdapter(__DIR__ . '/../../../database-test.sqlite');
        DatabaseConnection::setConnection($connection);
    }

    public function testExecute_ShouldConfirmPaymentInDatabase(): void
    {
        // arrange - prepare test
        $requestBody = [
            'payment' => [
                'type' => 'PHPUnit',
                'country' => 'BR',
                'amount' => 1552.48
            ],
            'customer' => [
                'name' => 'PHP',
                'email' => 'phpunit@email.com',
                'document' => '45687125963'
            ]
        ];

        $createPaymentService = new CreatePaymentService($requestBody);
        $payment = $createPaymentService->execute();
        $paymentId = $payment['id'];

        // act - run test
        $confirmPaymentService = new ConfirmPaymentService();
        $confirmPaymentService->execute($paymentId);

        // assert - check assert
        $lastInsertedPayment = $this->retrieveLastInsertedPayment();

        $this->assertEquals('confirmed', $lastInsertedPayment['status']);
        $this->assertEquals($lastInsertedPayment['amount'], $requestBody['payment']['amount']);
        $this->assertEquals($lastInsertedPayment['type'], $requestBody['payment']['type']);
        $this->assertEquals($lastInsertedPayment['country'], $requestBody['payment']['country']);
    }

    private function retrieveLastInsertedPayment(): array
    {
        $statement = new Statement("SELECT * FROM payments ORDER BY id DESC LIMIT 1");
        $statement->returningResults();

        return Database::execute($statement)->getRows()[0];
    }
}
