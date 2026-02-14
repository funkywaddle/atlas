<?php

namespace Atlas\Router;

use Psr\Http\Message\UriInterface;

/**
 * Represents a complete route definition with matching patterns, handlers, and metadata.
 *
 * @final
 */
final class RouteDefinition
{
    /**
     * Constructs a new RouteDefinition instance.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $pattern Matching pattern (currently not used for matching)
     * @param string $path Normalized path for comparison
     * @param mixed $handler Route handler
     * @param string|null $name Optional route name
     * @param array $middleware Middleware for route processing
     * @param array $validation Validation rules for route parameters
     * @param array $defaults Default parameter values
     * @param string|null $module Module identifier
     * @param array $attributes Route attributes for parameter extraction
     */
    public function __construct(
        private readonly string $method,
        private readonly string $pattern,
        private readonly string $path,
        private readonly mixed $handler,
        private readonly string|null $name = null,
        private readonly array $middleware = [],
        private readonly array $validation = [],
        private readonly array $defaults = [],
        private readonly string|null $module = null,
        private readonly array $attributes = []
    ) {}

    /**
     * Gets the HTTP method of this route definition.
     *
     * @return string HTTP method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Gets the path for this route definition.
     *
     * @return string Normalized path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Gets the handler for this route definition.
     *
     * @return string|callable Route handler
     */
    public function getHandler(): string|callable
    {
        return $this->handler;
    }

    /**
     * Gets the optional name of this route.
     *
     * @return string|null Route name or null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Gets the middleware configuration for this route.
     *
     * @return array Middleware configuration
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Gets the validation rules for this route.
     *
     * @return array Validation rules
     */
    public function getValidation(): array
    {
        return $this->validation;
    }

    /**
     * Gets the default values for parameters.
     *
     * @return array Default parameter values
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * Gets the module identifier for this route.
     *
     * @return string|null Module identifier or null
     */
    public function getModule(): ?string
    {
        return $this->module;
    }

    /**
     * Gets route attributes for parameter extraction.
     *
     * @return array Route attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Converts the route definition to an array.
     *
     * @return array Plain array representation
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'pattern' => $this->pattern,
            'path' => $this->path,
            'handler' => $this->handler,
            'name' => $this->name,
            'middleware' => $this->middleware,
            'validation' => $this->validation,
            'defaults' => $this->defaults,
            'module' => $this->module,
            'attributes' => $this->attributes
        ];
    }
}