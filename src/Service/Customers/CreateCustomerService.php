<?php

declare(strict_types=1);

namespace App\Service\Customers;

use Phractico\Core\Facades\Database;
use Phractico\Core\Facades\DatabaseOperation;
use Phractico\Core\Infrastructure\Database\Query\Statement;

class CreateCustomerService
{
    public function __construct(private readonly array $requestBody) {}

    public function getOrCreateCustomerId(): int
    {
        $email = $this->requestBody['customer']['email'];

        $statement = new Statement("SELECT id FROM customers WHERE email = '{$email}'");
        $statement->returningResults();

        $result = Database::execute($statement)->getRows();

        if (!empty($result)) {
            return $result[0]['id'];
        }

        $this->persistCustomer();
        return $this->getLastCustomerId();
    }

    private function persistCustomer(): void
    {
        $statement = DatabaseOperation::table('customers')
            ->insert()
            ->data($this->mappingValuesCustomers())
            ->build();

        Database::execute($statement);
    }

    private function mappingValuesCustomers(): array
    {
        return [
            'name' => $this->requestBody['customer']['name'],
            'email' => $this->requestBody['customer']['email'],
            'document' => $this->requestBody['customer']['document'],
        ];
    }

    private function getLastCustomerId(): int
    {
        $statement = new Statement("SELECT id FROM customers ORDER BY id DESC LIMIT 1");
        $statement->returningResults();

        return Database::execute($statement)->getRows()[0]['id'];
    }
}
