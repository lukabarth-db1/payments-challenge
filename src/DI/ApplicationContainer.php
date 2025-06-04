<?php

declare(strict_types=1);

namespace App\DI;

use App\API\Http\Controller\PaymentsController;
use App\API\Http\Provider\ApplicationControllerProvider;
use App\API\Http\Request\SymfonyHttpRequestInterceptor;
use App\Database\ApplicationDatabaseProvider;
use App\Gateway\Contracts\GatewayFactory;
use App\Gateway\Contracts\PaymentGateway;
use App\Gateway\PagueDificil;
use App\Gateway\PagueFacil;
use App\Service\Customers\CreateCustomerService;
use App\Service\Payments\CancelPaymentService;
use App\Service\Payments\ConfirmPaymentService;
use App\Service\Payments\CreatePaymentService;
use App\Service\Payments\PaymentStatusService;
use App\Service\Payments\RefundPaymentService;
use App\Service\Payments\Repository\PaymentRepository;
use App\Service\Payments\Repository\ProviderRepository;
use App\Service\Payments\RequestPaymentService;
use App\Service\Payments\RequestPaymentService\CancelPaymentHandler;
use App\Service\Payments\RequestPaymentService\ConfirmPaymentHandler;
use App\Service\Payments\RequestPaymentService\HandleRequestPayment;
use App\Service\Payments\RequestPaymentService\RefundPaymentHandler;
use App\Service\Providers\ProviderLogService;
use Phractico\Core\Infrastructure\Database\DatabaseProvider;
use Phractico\Core\Infrastructure\DI\Container;
use Phractico\Core\Infrastructure\DI\ContainerRegistry;
use Phractico\Core\Infrastructure\Http\ControllerProvider;
use Phractico\Core\Infrastructure\Http\Request\HttpRequestInterceptor;
use Psr\Container\ContainerInterface;

class ApplicationContainer
{
    public static function resolve(): ContainerInterface
    {
        $container = Container::create();
        $container->set(PagueFacil::class, fn() => new PagueFacil());
        $container->set(PagueDificil::class, fn() => new PagueDificil());

        $container->set(GatewayFactory::class, fn() => new GatewayFactory(
            $container->get(PagueFacil::class),
            $container->get(PagueDificil::class),
        ));
        $container->set(ProviderRepository::class, fn() => new ProviderRepository());
        $container->set(HttpRequestInterceptor::class, fn() => new SymfonyHttpRequestInterceptor());
        $container->set(ControllerProvider::class, fn() => new ApplicationControllerProvider());
        $container->set(DatabaseProvider::class, fn() => new ApplicationDatabaseProvider());
        $container->set(PaymentGateway::class, fn() => new PagueFacil());
        $container->set(ProviderLogService::class, fn() => new ProviderLogService());
        $container->set(CreateCustomerService::class, fn() => new CreateCustomerService());
        $container->set(CreatePaymentService::class, fn() => new CreatePaymentService());
        $container->set(PaymentStatusService::class, fn() => new PaymentStatusService());
        $container->set(ConfirmPaymentService::class, fn() => new ConfirmPaymentService(
            $container->get(PaymentGateway::class),
            $container->get(PaymentStatusService::class),
            $container->get(ProviderLogService::class),
        ));
        $container->set(CancelPaymentService::class, fn() => new CancelPaymentService(
            $container->get(PaymentGateway::class),
            $container->get(PaymentStatusService::class),
            $container->get(ProviderLogService::class),
        ));
        $container->set(RefundPaymentService::class, fn() => new RefundPaymentService(
            $container->get(PaymentGateway::class),
            $container->get(PaymentStatusService::class),
            $container->get(ProviderLogService::class),
        ));
        $container->set(HandleRequestPayment::class, fn() => new HandleRequestPayment(
            $container->get(RequestPaymentService::class)
        ));
        $container->set(ConfirmPaymentHandler::class, fn() => new ConfirmPaymentHandler(
            $container->get(ConfirmPaymentService::class),
            $container->get(ProviderRepository::class),
        ));
        $container->set(CancelPaymentHandler::class, fn() => new CancelPaymentHandler(
            $container->get(CancelPaymentService::class),
            $container->get(ProviderRepository::class),
        ));
        $container->set(RefundPaymentHandler::class, fn() => new RefundPaymentHandler(
            $container->get(RefundPaymentService::class),
            $container->get(ProviderRepository::class),
        ));
        $container->set(RequestPaymentService::class, fn() => new RequestPaymentService(
            $container->get(GatewayFactory::class),
            $container->get(ProviderLogService::class),
            $container->get(CreateCustomerService::class),
            $container->get(CreatePaymentService::class),
        ));
        $container->set(PaymentsController::class, fn() => new PaymentsController(
            $container->get(HandleRequestPayment::class),
            $container->get(ConfirmPaymentHandler::class),
            $container->get(CancelPaymentHandler::class),
            $container->get(RefundPaymentHandler::class),
        ));

        ContainerRegistry::clear();
        ContainerRegistry::setContainer($container);

        return $container;
    }
}
