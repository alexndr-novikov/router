<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests;

use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Action;
use Spiral\Router\Tests\Fixtures\TestController;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class SingleActionTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Router\Exception\UndefinedRouteException
     */
    public function testRouteException()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/test', new Action(TestController::class, 'test'))
        );

        $router->handle(new ServerRequest());
    }

    public function testRoute()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/test', new Action(TestController::class, 'test'))
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());
    }

    /**
     * @expectedException \Spiral\Router\Exception\UndefinedRouteException
     */
    public function testVerbRoute()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            (new Route('/test', new Action(TestController::class, 'test')))->withVerbs('POST')
        );

        $router->handle(new ServerRequest([], [], new Uri('/test')));

    }

    public function testVerbRouteValid()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            (new Route('/test', new Action(TestController::class, 'test')))->withVerbs('POST')
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test'), 'POST'));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());
    }

    public function testEchoed()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/test', new Action(TestController::class, 'echo'))
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("echoed", (string)$response->getBody());
    }

    public function testAutoFill()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/<action>', new Action(TestController::class, 'echo'))
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/echo')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("echoed", (string)$response->getBody());

        $e = null;
        try {
            $router->handle(new ServerRequest([], [], new Uri('/test')));
        } catch (UndefinedRouteException $e) {
        }

        $this->assertNotNull($e, 'Autofill not fired');
    }

    /**
     * @expectedException \Spiral\Router\Exception\RouteException
     */
    public function testVerbException()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            (new Route('/test', new Action(TestController::class, 'test')))->withVerbs('other')
        );
    }

    public function testParametrizedActionRoute()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/test/<id:\d+>', new Action(TestController::class, 'id'))
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test/100')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("100", (string)$response->getBody());
    }

    /**
     * @expectedException \Spiral\Router\Exception\UndefinedRouteException
     */
    public function testParametrizedActionRouteNotFound()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/test/<id:\d+>', new Action(TestController::class, 'id'))
        );

        $router->handle(new ServerRequest([], [], new Uri('/test/abc')));
    }

    public function testUriGeneration()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/test/<id>', new Action(TestController::class, 'id'))
        );

        $uri = $router->uri('action');
        $this->assertSame('/test', $uri->getPath());

        $uri = $router->uri('action', ['id' => 100]);
        $this->assertSame('/test/100', $uri->getPath());
    }

    /**
     * @expectedException \Spiral\Router\Exception\UndefinedRouteException
     */
    public function testWrongActionRoute()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/test', new Action(TestController::class, 'test'))
        );

        $router->handle(new ServerRequest([], [], new Uri('/other')));
    }
}