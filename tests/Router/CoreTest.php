<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests;

use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Router\CoreHandler;
use Spiral\Router\Target\Action;
use Spiral\Router\Tests\Fixtures\TestController;
use Zend\Diactoros\ServerRequest;

class CoreTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Router\Exception\TargetException
     */
    public function testMissingBinding()
    {
        $action = new Action(TestController::class, "test");

        $container = new Container();
        $action->getHandler($container, []);
    }

    public function testAutoCore()
    {
        $action = new Action(TestController::class, "test");
        $handler = $action->getHandler($this->container, []);

        $this->assertInstanceOf(CoreHandler::class, $handler);
    }

    public function testWithAutoCore()
    {
        $action = new Action(TestController::class, "test");

        $action->withCore(new TestCore($this->container->get(CoreInterface::class)));

        $handler = $action->getHandler($this->container, []);
        $this->assertInstanceOf(CoreHandler::class, $handler);


        $result = $handler->handle(new ServerRequest());

        $this->assertSame("@wrapped.hello world", (string)$result->getBody());
    }

    /**
     * @expectedException  \Error
     * @expectedExceptionMessage error.controller
     */
    public function testErrAction()
    {
        $action = new Action(TestController::class, "err");

        $action->withCore(new TestCore($this->container->get(CoreInterface::class)));

        $handler = $action->getHandler($this->container, []);
        $this->assertInstanceOf(CoreHandler::class, $handler);

        $handler->handle(new ServerRequest());
    }

    public function testRSP()
    {
        $action = new Action(TestController::class, "rsp");

        $handler = $action->getHandler($this->container, []);
        $this->assertInstanceOf(CoreHandler::class, $handler);

        $result = $handler->handle(new ServerRequest());

        $this->assertSame("rspbuf", (string)$result->getBody());
    }

    public function testJson()
    {
        $action = new Action(TestController::class, "json");

        $handler = $action->getHandler($this->container, []);
        $this->assertInstanceOf(CoreHandler::class, $handler);

        $result = $handler->handle(new ServerRequest());

        $this->assertSame(301, $result->getStatusCode());
        $this->assertSame('{"status":301,"msg":"redirect"}', (string)$result->getBody());
    }

    /**
     * @expectedException \Spiral\Http\Exception\ClientException\ForbiddenException
     */
    public function testForbidden()
    {
        $action = new Action(TestController::class, "forbidden");
        $r = $action->getHandler($this->container, [])->handle(new ServerRequest());
    }

    /**
     * @expectedException \Spiral\Http\Exception\ClientException\NotFoundException
     */
    public function testNotFound()
    {
        $action = new Action(TestController::class, "not-found");
        $r = $action->getHandler($this->container, [])->handle(new ServerRequest());
    }

    /**
     * @expectedException \Spiral\Http\Exception\ClientException\BadRequestException
     */
    public function testBadRequest()
    {
        $action = new Action(TestController::class, "weird");
        $r = $action->getHandler($this->container, [])->handle(new ServerRequest());
    }

    /**
     * @expectedException \Spiral\Router\Exception\HandlerException
     */
    public function testCoreException()
    {
        /** @var CoreHandler $core */
        $core = $this->container->get(CoreHandler::class);
        $core->handle(new ServerRequest());
    }

    public function testRESTFul()
    {
        $action = new Action(TestController::class, 'Target', Action::RESTFUL);
        $r = $action->getHandler($this->container, [])->handle(new ServerRequest(
            [],
            [],
            '',
            'POST'
        ));

        $this->assertSame('POST', (string)$r->getBody());

        $r = $action->getHandler($this->container, [])->handle(new ServerRequest(
            [],
            [],
            '',
            'DELETE'
        ));

        $this->assertSame('DELETE', (string)$r->getBody());
    }
}

class TestCore implements CoreInterface
{
    private $core;

    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }

    public function callAction(
        string $controller,
        string $action = null,
        array $parameters = [],
        array $scope = []
    ) {
        return "@wrapped." . $this->core->callAction($controller, $action, $parameters, $scope);
    }
}