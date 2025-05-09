<?php

namespace App\Tests;

use App\Application;
use App\Tests\Helpers\API\Http\FakeController;
use App\Tests\Helpers\Database\Connection\DummyDatabase;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Phractico\Core\Infrastructure\Database\DatabaseProvider;
use Phractico\Core\Infrastructure\DI\Container;
use Phractico\Core\Infrastructure\DI\ContainerRegistry;
use Phractico\Core\Infrastructure\Http\ControllerProvider;
use Phractico\Core\Infrastructure\Http\Request\HttpRequestInterceptor;

class ApplicationTest extends TestCase
{
    public function testApplication(): void
    {
        $controllerProviderStub = $this->createStub(ControllerProvider::class);
        $controllerProviderStub
            ->method('getControllers')
            ->willReturn([get_class($fakeController = new FakeController())]);

        $databaseProviderStub = $this->createStub(DatabaseProvider::class);
        $databaseProviderStub
            ->method('getConnection')
            ->willReturn(new DummyDatabase());

        $httpRequestInterceptorStub = $this->createStub(HttpRequestInterceptor::class);
        $httpRequestInterceptorStub
            ->method('intercept')
            ->willReturn(new Request('POST', '/fake'));

        $container = Container::create();
        $container->set(FakeController::class, fn() => new FakeController());
        $container->set(ControllerProvider::class, fn() => $controllerProviderStub);
        $container->set(DatabaseProvider::class, fn() => $databaseProviderStub);
        $container->set(HttpRequestInterceptor::class, fn() => $httpRequestInterceptorStub);
        ContainerRegistry::setContainer($container);

        $fakeControllerResponse = $fakeController->fake()->render();
        $this->expectOutputString($fakeControllerResponse->getBody()->getContents());

        $application = new Application($container);
        $application->run();
    }
}
