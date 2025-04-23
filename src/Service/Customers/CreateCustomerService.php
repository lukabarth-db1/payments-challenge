<?php

declare(strict_types=1);

namespace App\Service\Customers;

use App\Service\Customers\Dto\CreateCustomerInfo;
use Phractico\Core\Facades\Database;
use Phractico\Core\Facades\DatabaseOperation;
use Phractico\Core\Infrastructure\Database\Query\Statement;

class CreateCustomerService
{
    public function getOrCreateCustomerId(CreateCustomerInfo $customerInfo): int
    {
        $email = $customerInfo->email;

        $statement = new Statement("SELECT id FROM customers WHERE email = '{$email}'");
        $statement->returningResults();

        $result = Database::execute($statement)->getRows();

        if (!empty($result)) {
            return $result[0]['id'];
        }

        $this->persistCustomer($customerInfo);
        return $this->getLastCustomerId();
    }

    private function persistCustomer(CreateCustomerInfo $customerInfo): void
    {
        $statement = DatabaseOperation::table('customers')
            ->insert()
            ->data($this->mappingValuesCustomers($customerInfo))
            ->build();

        Database::execute($statement);
    }

    private function mappingValuesCustomers(CreateCustomerInfo $customerInfo): array
    {
        return [
            'name' => $customerInfo->name,
            'email' => $customerInfo->email,
            'document' => $customerInfo->document,
        ];
    }

    private function getLastCustomerId(): int
    {
        $statement = new Statement("SELECT id FROM customers ORDER BY id DESC LIMIT 1");
        $statement->returningResults();

        return Database::execute($statement)->getRows()[0]['id'];
    }
}
