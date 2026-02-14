<?php

namespace Atlas\Router;

/**
 * Manages groupings of routes for prefix and middleware organization.
 *
 * @implements \IteratorAggregate<array-key, mixed>
 */
class RouteGroup
{
    /**
     * Constructs a new RouteGroup instance.
     *
     * @param array $options Group options including 'prefix' and optional middleware
     * @param Router|null $router Optional parent router instance
     */
    public function __construct(
        private array $options = [],
        private readonly Router|null $router = null
    ) {}

    /**
     * Creates a new route group with options and router.
     *
     * @param array $options Group options including 'prefix' and optional middleware
     * @param Router $router Parent router instance
     * @return self New instance configured with router
     */
    public static function create(array $options, Router $router): self
    {
        $self = new self($options);
        $self->router = $router;
        return $self;
    }

    /**
     * Registers a GET route with group prefix.
     *
     * @param string $path URI path
     * @param string|callable $handler Route handler
     * @param string|null $name Optional route name
     * @return self Fluent interface
     */
    public function get(string $path, string|callable $handler, string|null $name = null): self
    {
        $fullPath = $this->buildFullPath($path);
        return $this->router ? $this->router->get($fullPath, $handler, $name) : $this;
    }

    /**
     * Registers a POST route with group prefix.
     *
     * @param string $path URI path
     * @param string|callable $handler Route handler
     * @param string|null $name Optional route name
     * @return self Fluent interface
     */
    public function post(string $path, string|callable $handler, string|null $name = null): self
    {
        $fullPath = $this->buildFullPath($path);
        return $this->router ? $this->router->post($fullPath, $handler, $name) : $this;
    }

    /**
     * Registers a PUT route with group prefix.
     *
     * @param string $path URI path
     * @param string|callable $handler Route handler
     * @param string|null $name Optional route name
     * @return self Fluent interface
     */
    public function put(string $path, string|callable $handler, string|null $name = null): self
    {
        $fullPath = $this->buildFullPath($path);
        return $this->router ? $this->router->put($fullPath, $handler, $name) : $this;
    }

    /**
     * Registers a PATCH route with group prefix.
     *
     * @param string $path URI path
     * @param string|callable $handler Route handler
     * @param string|null $name Optional route name
     * @return self Fluent interface
     */
    public function patch(string $path, string|callable $handler, string|null $name = null): self
    {
        $fullPath = $this->buildFullPath($path);
        return $this->router ? $this->router->patch($fullPath, $handler, $name) : $this;
    }

    /**
     * Registers a DELETE route with group prefix.
     *
     * @param string $path URI path
     * @param string|callable $handler Route handler
     * @param string|null $name Optional route name
     * @return self Fluent interface
     */
    public function delete(string $path, string|callable $handler, string|null $name = null): self
    {
        $fullPath = $this->buildFullPath($path);
        return $this->router ? $this->router->delete($fullPath, $handler, $name) : $this;
    }

    /**
     * Builds the full path with group prefix.
     *
     * @param string $path Route path without prefix
     * @return string Complete path with prefix
     */
    private function buildFullPath(string $path): string
    {
        $prefix = $this->options['prefix'] ?? '';

        if (empty($prefix)) {
            return $path;
        }

        return rtrim($prefix, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Sets an option value.
     *
     * @param string $key Option key
     * @param mixed $value Option value
     * @return self Fluent interface
     */
    public function setOption(string $key, mixed $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * Gets all group options.
     *
     * @return array Group options configuration
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}