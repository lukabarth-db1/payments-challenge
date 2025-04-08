<?php

declare(strict_types=1);

namespace App\API\Http\Controller;

use Phractico\Core\Facades\Database;
use Phractico\Core\Facades\DatabaseOperation;
use Phractico\Core\Infrastructure\Database\Query\Statement;
use Phractico\Core\Infrastructure\Database\Result;
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

    public function getRequestBody(): void
    {
        // Pegando o corpo da requisição
        $request = RequestHandler::getIncomingRequest();
        $contents = $request->getBody()->getContents();
        $this->requestBody = json_decode($contents, true);
    }

    public function mappingValuesCustomer(): array
    {
        // Mapeando valores do customer para a tabela customer do banco de dados
        return [
            'name' => $this->requestBody['customer']['name'],
            'email' => $this->requestBody['customer']['email'],
            'document' => $this->requestBody['customer']['document'],
        ];
    }

    public function prepareStatementCustomer(): void
    {
        // Preparando statement para inserção dos valores na tabela customer do banco de dados
        $statement = DatabaseOperation::table('customers')
            ->insert()
            ->data($this->mappingValuesCustomer())
            ->build();
        Database::execute($statement);
    }

    public function getCustomerId(): int
    {
        // Recuperar valor do id na tabela customer do banco de dados
        $statement = new Statement(
            "SELECT * FROM customers ORDER BY id DESC LIMIT 1"
        );
        $statement->returningResults();
        $result = Database::execute($statement);

        return $result->getRows()[0]['id'];
    }

    public function mappingValuesPayments(): array
    {
        // Mapeando valores para tabela de pagamentos
        return [
            'amount' => $this->requestBody['payment']['amount'],
            'type' => $this->requestBody['payment']['type'],
            'country' => $this->requestBody['payment']['country'],
            'customer_id' => $this->getCustomerId(),
        ];
    }

    public function prepareStatementPayment(): void
    {
        // Preparando statement para inserção dos valores na tabela payment do banco de dados
        $statement = DatabaseOperation::table('payments')
            ->insert()
            ->data($this->mappingValuesPayments())
            ->build();
        Database::execute($statement);
    }

    public function getPaymentValue(): Result
    {
        // Recuperando valor payment do banco de dados
        $statement = new Statement(
            "SELECT * FROM payments ORDER BY id DESC LIMIT 1"
        );
        $statement->returningResults();

        return Database::execute($statement);
    }

    public function responseBodyInJson(): Response
    {
        // Preparando a resposta da requisição
        $body = [
            $this->getPaymentValue()->getRows()
        ];
        return new JsonResponse(201, $body);
    }

    public function createPayment(): Response
    {
        $this->getRequestBody();
        $this->prepareStatementCustomer();
        $this->prepareStatementPayment();
        return $this->responseBodyInJson();
    }
}
