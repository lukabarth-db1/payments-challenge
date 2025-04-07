<?php

declare(strict_types=1);

namespace App\API\Http\Controller;

use Phractico\Core\Facades\Database;
use Phractico\Core\Facades\DatabaseOperation;
use Phractico\Core\Infrastructure\Database\Query\Grammar\Comparison;
use Phractico\Core\Infrastructure\Database\Query\Statement;
use Phractico\Core\Infrastructure\Http\Controller;
use Phractico\Core\Infrastructure\Http\Request\RequestHandler;
use Phractico\Core\Infrastructure\Http\Request\Route;
use Phractico\Core\Infrastructure\Http\Request\RouteCollection;
use Phractico\Core\Infrastructure\Http\Response;
use Phractico\Core\Infrastructure\Http\Response\JsonResponse;

class PaymentsController implements Controller
{
    public function routes(): RouteCollection
    {
        $routes = RouteCollection::for($this);
        $routes->add(Route::create('POST', '/requestPayment'), 'createPayment');
        return $routes;
    }

    public function createPayment(): Response
    {
        // Pegando o corpo da requisição
        $request = RequestHandler::getIncomingRequest();
        $requestBody = $request->getBody()->getContents();
        $decodedRequestBody = json_decode($requestBody, true);

        // Mapeando valores do customer para a tabela customer do banco de dados
        $customerData = [
            'name' => $decodedRequestBody['customer']['name'],
            'email' => $decodedRequestBody['customer']['email'],
            'document' => $decodedRequestBody['customer']['document'],
        ];

        // Preparando statement para inserção dos valores na tabela customer do banco de dados
        $statement = DatabaseOperation::table('customers')
            ->insert()
            ->data($customerData)
            ->build();
        Database::execute($statement);

        // Recuperar valor do customer do banco de dados
        $statement = new Statement(
            "SELECT * FROM customers ORDER BY id DESC LIMIT 1"
        );
        $statement->returningResults();
        $customerSelectResult = Database::execute($statement);

        // Acessando o id do customer
        $customer_id = $customerSelectResult->getRows()[0]['id'];

        // Mapeando valores para tabela de pagamentos
        $paymentData = [
            'amount' => $decodedRequestBody['payment']['amount'],
            'type' => $decodedRequestBody['payment']['type'],
            'country' => $decodedRequestBody['payment']['country'],
            'customer_id' => $customer_id,
        ];

        // Preparando statement para inserção dos valores na tabela payment do banco de dados
        $statement = DatabaseOperation::table('payments')
            ->insert()
            ->data($paymentData)
            ->build();
        Database::execute($statement);

        // Recuperando valor payment do banco de dados
        $statement = new Statement(
            "SELECT * FROM payments ORDER BY id DESC LIMIT 1"
        );
        $statement->returningResults();
        $paymentSelectResult = Database::execute($statement);

        // Preparando a resposta da requisição
        $body = [
            $paymentSelectResult->getRows()
        ];
        return new JsonResponse(201, $body);
    }
}
