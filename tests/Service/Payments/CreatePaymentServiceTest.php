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
        $connection = new SQLiteAdapter(__DIR__ . '/../../../database-test.sqlite');
        DatabaseConnection::setConnection($connection);
    }

    public function testExecute_ShouldPersistPaymentInDatabase(): void
    {
        // arrange - prepare test
        $requestBody = [
            'payment' => [
                'type' => 'PHPUnitConfirm',
                'country' => 'BR',
                'amount' => 1552.48
            ],
            'customer' => [
                'name' => 'PHP Confirm',
                'email' => 'phpunitconfirm@email.com',
                'document' => '45687125963'
            ]
        ];

        $createPaymentService = new CreatePaymentService($requestBody);

        // act - run test
        $createPaymentService->execute();

        // assert - check assert
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
