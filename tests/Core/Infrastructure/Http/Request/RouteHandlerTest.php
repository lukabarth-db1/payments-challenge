<?php

namespace Phractico\Tests\Core\Infrastructure\Http\Request;

use App\Tests\Helpers\API\Http\FakeController;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Phractico\Core\Infrastructure\DI\Container;
use Phractico\Core\Infrastructure\DI\ContainerRegistry;
use Phractico\Core\Infrastructure\Http\Request\RouteHandler;

class RouteHandlerTest extends TestCase
{
    public function testHandleShouldRouteRequestToExpectedController(): void
    {
        $fakeController = new FakeController();
        $controllerMapping = [get_class($fakeController)];
        $container = Container::create();
        $container->set(FakeController::class, fn() => new FakeController());
        ContainerRegistry::setContainer($container);
        RouteHandler::init($controllerMapping);

        $request = new Request('POST', '/fake');
        $response = RouteHandler::handle($request);
        $responseBody = $response->getBody()->getContents();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($responseBody);
        $this->assertEquals(json_encode(['message' => 'FakeController']), $responseBody);
    }

    public function testHandleShouldReturnInternalServerErrorOnUndefinedControllerMapping(): void
    {
        $container = Container::create();
        ContainerRegistry::setContainer($container);

        $controllerMapping = [];
        RouteHandler::init($controllerMapping);

        $request = new Request('POST', '/fake');
        $response = RouteHandler::handle($request);
        $responseBody = $response->getBody()->getContents();

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertJson($responseBody);
        $this->assertEquals(json_encode(['error' => 'Internal Server Error']), $responseBody);
    }
}
