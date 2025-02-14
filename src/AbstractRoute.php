<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Router;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Spiral\Router\Traits\DefaultsTrait;
use Spiral\Router\Traits\VerbsTrait;

abstract class AbstractRoute implements RouteInterface
{
    use VerbsTrait, DefaultsTrait;

    /** @var UriHandler */
    protected $uriHandler;

    /** @var string */
    protected $pattern;

    /** @var array|null */
    protected $matches = null;

    /**
     * @param string $pattern
     * @param array  $defaults
     */
    public function __construct(string $pattern, array $defaults = [])
    {
        $this->pattern = $pattern;
        $this->defaults = $defaults;
    }

    /**
     * @param UriHandler $uriHandler
     * @return RouteInterface
     */
    public function withUriHandler(UriHandler $uriHandler): RouteInterface
    {
        $route = clone $this;
        $route->uriHandler = $uriHandler->withPattern($this->pattern);

        return $route;
    }

    /**
     * @inheritdoc
     */
    public function getUriHandler(): UriHandler
    {
        return $this->uriHandler;
    }

    /**
     * @inheritdoc
     */
    public function match(Request $request): ?RouteInterface
    {
        if (!in_array(strtoupper($request->getMethod()), $this->getVerbs())) {
            return null;
        }

        $matches = $this->uriHandler->match($request->getUri(), $this->defaults);
        if ($matches === null) {
            return null;
        }

        $route = clone $this;
        $route->matches = $matches;

        return $route;
    }

    /**
     * @inheritdoc
     */
    public function getMatches(): ?array
    {
        return $this->matches;
    }

    /**
     * @inheritdoc
     */
    public function uri($parameters = []): UriInterface
    {
        return $this->uriHandler->uri(
            $parameters,
            array_merge($this->defaults, $this->matches ?? [])
        );
    }
}