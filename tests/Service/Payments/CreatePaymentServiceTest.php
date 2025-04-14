<?php

namespace App\Tests\Service;

use App\Database\Connection\SQLiteAdapter;
use App\Service\Payments\CreatePaymentService;
use PHPUnit\Framework\TestCase;
use Phractico\Core\Facades\Database;
use Phractico\Core\Infrastructure\Database\DatabaseConnection;
use Phractico\Core\Infrastructure\Database\Query\Statement;

class CreatePaymentServiceTest extends TestCase
{
    /**
     * @before
     */
    public function init(): void
    {
        $connection = new SQLiteAdapter(__DIR__ . '/../../database-test.sqlite');
        DatabaseConnection::setConnection($connection);
    }

    public function testExecute_ShouldPersistPaymentInDatabase(): void
    {
        // arrange
        $requestBody = [
            'payment' => [
                'type' => 'fake',
                'country' => 'BR',
                'amount' => 12.50
            ],
            'customer' => [
                'name' => 'Luka',
                'email' => 'luka@email.com',
                'document' => '12345678122'
            ]
        ];

        $createPaymentService = new CreatePaymentService($requestBody);

        // act
        $createPaymentService->execute();

        // assert
        $lastInsertedPayment = $this->retrieveLastInsertedPayment();

        $this->assertEquals('pending', $lastInsertedPayment['status']);
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
