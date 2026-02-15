<?php

namespace Atlas\Router;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Represents a single route definition with its HTTP method, path, and handler.
 *
 * Though not currently used for matching, this class provides a base structure
 * for routes and may be extended for future matching capabilities.
 *
 * @final
 */
final class Route
{
    /**
     * Constructs a new Route instance.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $path URI path
     * @param mixed $handler Route handler or string reference
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly mixed $handler
    ) {
    }

    /**
     * Gets the HTTP method of this route.
     *
     * @return string HTTP method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Gets the URI path of this route.
     *
     * @return string URI path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Gets the handler for this route.
     *
     * @return mixed Route handler
     */
    public function getHandler(): mixed
    {
        return $this->handler;
    }
}
