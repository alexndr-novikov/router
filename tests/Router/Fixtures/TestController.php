<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests\Fixtures;

use Spiral\Core\Controller;
use Spiral\Core\Exception\ControllerException;
use Zend\Diactoros\Response;

class TestController extends Controller
{
    protected $defaultAction = 'test';

    public function testAction()
    {
        return "hello world";
    }

    public function idAction(string $id)
    {
        return $id;
    }

    public function echoAction()
    {
        ob_start();
        echo "echoed";
    }

    public function errAction()
    {
        throw new \Error("error.controller");
    }

    public function rspAction()
    {
        $r = new Response();
        $r->getBody()->write("rsp");

        echo "buf";

        return $r;
    }

    public function jsonAction()
    {
        return [
            'status' => 301,
            'msg'    => 'redirect'
        ];
    }

    public function forbiddenAction()
    {
        throw new ControllerException("", ControllerException::FORBIDDEN);
    }

    public function notFoundAction()
    {
        throw new ControllerException("", ControllerException::NOT_FOUND);
    }

    public function weirdAction()
    {
        throw new ControllerException("", 99);
    }

    public function postTargetAction()
    {
        return 'POST';
    }

    public function deleteTargetAction()
    {
        return 'DELETE';
    }
}