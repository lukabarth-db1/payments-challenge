<?php

declare(strict_types=1);

namespace App\API\Http\Controller;

use App\Service\Customers\Dto\CreateCustomerInfo;
use App\Service\Dto\RequestPaymentData;
use App\Service\Payments\RequestPaymentService\HandleRequestPayment;
use App\Service\Payments\RequestPaymentService\ConfirmPaymentHandler;
use App\Service\Payments\RequestPaymentService\CancelPaymentHandler;
use App\Service\Payments\RequestPaymentService\RefundPaymentHandler;
use DomainException;
use GuzzleHttp\Psr7\Request;
use Phractico\Core\Infrastructure\Http\Controller;
use Phractico\Core\Infrastructure\Http\Request\RequestHandler;
use Phractico\Core\Infrastructure\Http\Request\Route;
use Phractico\Core\Infrastructure\Http\Request\RouteCollection;
use Phractico\Core\Infrastructure\Http\Response;
use Phractico\Core\Infrastructure\Http\Response\JsonResponse;

class PaymentsController implements Controller
{
    public function __construct(
        private HandleRequestPayment $handleRequestPayment,
        private ConfirmPaymentHandler $confirmPaymentHandler,
        private CancelPaymentHandler $cancelPaymentHandler,
        private RefundPaymentHandler $refundPaymentHandler,
    ) {}

    private array $requestBody = [];

    public function routes(): RouteCollection
    {
        $routes = RouteCollection::for($this);
        $routes->add(Route::create('POST', '/requestPayment'), 'createPayment');
        $routes->add(Route::create('POST', '/confirmPayment'), 'confirmPayment');
        $routes->add(Route::create('POST', '/cancelPayment'), 'cancelPayment');
        $routes->add(Route::create('POST', '/refundPayment'), 'refundPayment');
        return $routes;
    }

    public function createPayment(): Response
    {
        try {
            $request = RequestHandler::getIncomingRequest();
            $this->requestBody = $this->decodeRequestBody($request);

            $dto = new RequestPaymentData(
                paymentAmount: $this->requestBody['payment']['amount'],
                paymentType: $this->requestBody['payment']['type'],
                paymentCountry: $this->requestBody['payment']['country'],
                customer: new CreateCustomerInfo(
                    $this->requestBody['customer']['name'],
                    $this->requestBody['customer']['email'],
                    $this->requestBody['customer']['document'],
                ),
            );

            $payment = ($this->handleRequestPayment)($dto);

            return new JsonResponse(201, [
                'payment' => $payment,
                'provider_logs' => "Payment successfuly created",
            ]);
        } catch (DomainException $e) {
            return new JsonResponse(400, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function confirmPayment(): Response
    {
        $request = RequestHandler::getIncomingRequest();
        $this->requestBody = $this->decodeRequestBody($request);

        try {
            ($this->confirmPaymentHandler)($this->requestBody['payment']['id']);

            return new JsonResponse(200, [
                'message' => 'payment confirmed'
            ]);
        } catch (DomainException $e) {
            return new JsonResponse(400, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cancelPayment(): Response
    {
        $request = RequestHandler::getIncomingRequest();
        $this->requestBody = $this->decodeRequestBody($request);

        try {
            ($this->cancelPaymentHandler)($this->requestBody['payment']['id']);

            return new JsonResponse(200, [
                'message' => 'payment canceled'
            ]);
        } catch (DomainException $e) {
            return new JsonResponse(400, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function refundPayment(): Response
    {
        $request = RequestHandler::getIncomingRequest();
        $this->requestBody = $this->decodeRequestBody($request);

        try {
            ($this->refundPaymentHandler)($this->requestBody['payment']['id']);

            return new JsonResponse(200, [
                'message' => 'payment refunded'
            ]);
        } catch (DomainException $e) {
            return new JsonResponse(400, [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function decodeRequestBody(Request $request): array
    {
        $contents = $request->getBody()->getContents();
        return json_decode($contents, true);
    }
}
