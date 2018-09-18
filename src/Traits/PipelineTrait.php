<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Traits;

use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Server\MiddlewareInterface;
use Spiral\Http\Pipeline;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\RouteInterface;

trait PipelineTrait
{
    use ContainerTrait;

    /** @var Pipeline */
    protected $pipeline;

    /** @var MiddlewareInterface|string */
    protected $middleware = [];

    /**
     * Associated middleware with route. New instance of route will be returned.
     *
     * Example:
     * $route->withMiddleware(new CacheMiddleware(100));
     * $route->withMiddleware(ProxyMiddleware::class);
     * $route->withMiddleware(ProxyMiddleware::class, OtherMiddleware::class);
     * $route->withMiddleware([ProxyMiddleware::class, OtherMiddleware::class]);
     *
     * @param MiddlewareInterface|string|array ...$middleware
     *
     * @return RouteInterface|$this
     *
     * @throws RouteException
     */
    public function withMiddleware(...$middleware): RouteInterface
    {
        $route = clone $this;

        // array fallback
        if (count($middleware) == 1 && is_array($middleware[0])) {
            $middleware = $middleware[0];
        }

        foreach ($middleware as $item) {
            if (!is_string($item) && !$item instanceof MiddlewareInterface) {
                if (is_object($item)) {
                    $name = get_class($item);
                } else {
                    $name = gettype($item);
                }

                throw new RouteException("Invalid middleware `{$name}`.");
            }

            $route->middleware[] = $item;
        }

        if (!empty($route->pipeline)) {
            $route->pipeline = $route->makePipeline();
        }

        return $route;
    }

    /**
     * Get associated route pipeline.
     *
     * @return Pipeline
     * @throws RouteException
     */
    protected function makePipeline(): Pipeline
    {
        try {
            $this->pipeline = $this->container->get(Pipeline::class);

            foreach ($this->middleware as $middleware) {
                if ($middleware instanceof MiddlewareInterface) {
                    $this->pipeline->pushMiddleware($middleware);
                } else {
                    // dynamically resolved
                    $this->pipeline->pushMiddleware($this->container->get($middleware));
                }
            }
        } catch (ContainerExceptionInterface $e) {
            throw new RouteException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->pipeline;
    }
}