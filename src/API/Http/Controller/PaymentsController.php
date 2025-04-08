<?php

declare(strict_types=1);

namespace App\API\Http\Controller;

use GuzzleHttp\Psr7\Request;
use Phractico\Core\Facades\Database;
use Phractico\Core\Facades\DatabaseOperation;
use Phractico\Core\Infrastructure\Database\Query\Statement;
use Phractico\Core\Infrastructure\Http\Controller;
use Phractico\Core\Infrastructure\Http\Request\RequestHandler;
use Phractico\Core\Infrastructure\Http\Request\Route;
use Phractico\Core\Infrastructure\Http\Request\RouteCollection;
use Phractico\Core\Infrastructure\Http\Response;
use Phractico\Core\Infrastructure\Http\Response\JsonResponse;

class PaymentsController implements Controller
{
    private array $requestBody = [];

    public function routes(): RouteCollection
    {
        $routes = RouteCollection::for($this);
        $routes->add(Route::create('POST', '/requestPayment'), 'createPayment');
        return $routes;
    }

    public function createPayment(): Response
    {
        $request = RequestHandler::getIncomingRequest();
        $this->requestBody = $this->decodeRequestBody($request);

        $this->persistCustomer();
        $this->persistPayment();

        $payment = $this->retrieveLastInsertedPayment();

        return new JsonResponse(201, [
            'payment' => $payment
        ]);
    }

    private function decodeRequestBody(Request $request): array
    {
        $contents = $request->getBody()->getContents();
        return json_decode($contents, true);
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
            'customer_id' => $this->getLastCustomerId(),
        ];
    }

    private function getLastCustomerId(): int
    {
        $statement = new Statement("SELECT id FROM customers ORDER BY id DESC LIMIT 1");
        $statement->returningResults();

        return Database::execute($statement)->getRows()[0]['id'];
    }

    private function retrieveLastInsertedPayment(): array
    {
        $statement = new Statement("SELECT * FROM payments ORDER BY id DESC LIMIT 1");
        $statement->returningResults();

        return Database::execute($statement)->getRows()[0];
    }
}
