<?php

namespace App\Tests\Service;

use App\Database\Connection\SQLiteAdapter;
use App\Service\Payments\CreatePaymentService;
use App\Service\Payments\Dto\CreatePaymentInfo;
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
        $paymentInfo = new CreatePaymentInfo(
            amount: 2.550,
            type: 'creditcard',
            country: 'br',
            customerId: 1,
        );

        $createPaymentService = new CreatePaymentService($paymentInfo);

        // act - run test
        $createPaymentService->execute($paymentInfo);

        // assert - check assert
        $lastInsertedPayment = $this->retrieveLastInsertedPayment();

        $this->assertEquals('pending', $lastInsertedPayment['status']);
        $this->assertEquals($lastInsertedPayment['amount'], $paymentInfo->amount);
        $this->assertEquals($lastInsertedPayment['type'], $paymentInfo->type);
        $this->assertEquals($lastInsertedPayment['country'], $paymentInfo->country);
    }

    private function retrieveLastInsertedPayment(): array
    {
        $statement = new Statement("SELECT * FROM payments ORDER BY id DESC LIMIT 1");
        $statement->returningResults();

        return Database::execute($statement)->getRows()[0];
    }
}
