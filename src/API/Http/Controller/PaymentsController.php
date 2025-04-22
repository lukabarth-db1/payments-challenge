<?php

declare(strict_types=1);

namespace App\API\Http\Controller;

use App\Gateway\Contracts\PaymentGatewayInterface;
use App\Gateway\PagueFacil;
use App\Service\Customers\CreateCustomerService;
use App\Service\Payments\CancelPaymentService;
use App\Service\Payments\ConfirmPaymentService;
use App\Service\Payments\CreatePaymentService;
use App\Service\Payments\PaymentStatusService;
use App\Service\Payments\RefundPaymentService;
use App\Service\Payments\RequestPaymentService;
use App\Service\Providers\ProviderLogService;
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
        private PaymentGatewayInterface $paymentGateway = new PagueFacil(),
        private ProviderLogService $providerLogService = new ProviderLogService(),
        private PaymentStatusService $paymentStatusService = new PaymentStatusService(),
        private CreateCustomerService $createCustomerService = new CreateCustomerService([]),
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
        $request = RequestHandler::getIncomingRequest();
        $this->requestBody = $this->decodeRequestBody($request);

        $paymentService = new RequestPaymentService(
            $this->paymentGateway,
            $this->providerLogService,
        );

        $payment = $paymentService->handle($this->requestBody);

        return new JsonResponse(201, [
            'payment' => $payment,
            'provider_logs' => "Payment successfuly created",
        ]);
    }

    public function confirmPayment(): Response
    {
        $request = RequestHandler::getIncomingRequest();
        $this->requestBody = $this->decodeRequestBody($request);

        $paymentId = $this->requestBody['payment']['id'];

        try {
            $service = new ConfirmPaymentService($this->paymentStatusService);
            $service->execute($paymentId);

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

        $paymentId = $this->requestBody['payment']['id'];

        try {
            $service = new CancelPaymentService($this->paymentStatusService);
            $service->execute($paymentId);
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

        $paymentId = $this->requestBody['payment']['id'];

        try {
            $service = new RefundPaymentService($this->paymentStatusService);
            $service->execute($paymentId);
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
