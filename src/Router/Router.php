<?php

namespace Atlas\Router;

use Psr\Http\Message\ServerRequestInterface;
use Atlas\Config\Config;
use Atlas\Exception\MissingConfigurationException;
use Atlas\Exception\NotFoundRouteException;
use Atlas\Exception\RouteNotFoundException;

/**
 * Main routing engine for the Atlas framework.
 *
 * Provides fluent, chainable API for route registration and request matching.
 * Supports static and dynamic URIs, named routes, module routing, and error handling.
 *
 * @implements \IteratorAggregate<array-key, RouteDefinition>
 */
class Router implements \IteratorAggregate
{
    /**
     * Private array to store registered route definitions.
     *
     * @var array<RouteDefinition>
     */
    private array $routes = [];

    /**
     * Protected fallback handler for 404 scenarios.
     *
     * @var mixed mixed callable|string|null
     */
    protected mixed $fallbackHandler = null;

    /**
     * Constructs a new Router instance with configuration.
     *
     * @param Config\Config $config Configuration object containing routing settings
     */
    public function __construct(
        private readonly Config\Config $config
    ) {
    }

    /**
     * Registers a GET route.
     *
     * @param string $path URI path
     * @param string|callable $handler Route handler or string reference
     * @param string|null $name Optional route name for reverse routing
     * @return self Fluent interface for method chaining
     */
    public function get(string $path, string|callable $handler, string|null $name = null): self
    {
        $this->registerRoute('GET', $path, $handler, $name);
        return $this;
    }

    /**
     * Registers a POST route.
     *
     * @param string $path URI path
     * @param string|callable $handler Route handler or string reference
     * @param string|null $name Optional route name for reverse routing
     * @return self Fluent interface for method chaining
     */
    public function post(string $path, string|callable $handler, string|null $name = null): self
    {
        $this->registerRoute('POST', $path, $handler, $name);
        return $this;
    }

    /**
     * Registers a PUT route.
     *
     * @param string $path URI path
     * @param string|callable $handler Route handler or string reference
     * @param string|null $name Optional route name for reverse routing
     * @return self Fluent interface for method chaining
     */
    public function put(string $path, string|callable $handler, string|null $name = null): self
    {
        $this->registerRoute('PUT', $path, $handler, $name);
        return $this;
    }

    /**
     * Registers a PATCH route.
     *
     * @param string $path URI path
     * @param string|callable $handler Route handler or string reference
     * @param string|null $name Optional route name for reverse routing
     * @return self Fluent interface for method chaining
     */
    public function patch(string $path, string|callable $handler, string|null $name = null): self
    {
        $this->registerRoute('PATCH', $path, $handler, $name);
        return $this;
    }

    /**
     * Registers a DELETE route.
     *
     * @param string $path URI path
     * @param string|callable $handler Route handler or string reference
     * @param string|null $name Optional route name for reverse routing
     * @return self Fluent interface for method chaining
     */
    public function delete(string $path, string|callable $handler, string|null $name = null): self
    {
        $this->registerRoute('DELETE', $path, $handler, $name);
        return $this;
    }

    /**
     * Normalizes a path string.
     *
     * Removes leading and trailing slashes and ensures proper format.
     *
     * @param string $path Raw path string
     * @return string Normalized path
     */
    private function normalizePath(string $path): string
    {
        $normalized = trim($path, '/');

        if (empty($normalized)) {
            return '/';
        }

        return '/' . $normalized;
    }

    /**
     * Registers a route definition and stores it.
     *
     * @param string $method HTTP method
     * @param string $path URI path
     * @param mixed $middleware middleware (not yet implemented)
     * @param string|callable $handler Route handler
     * @param string|null $name Optional route name
     */
    private function registerRoute(string $method, string $path, mixed $middleware, string|callable $handler, string|null $name = null): void
    {
        $routeDefinition = new RouteDefinition(
            $method,
            $this->normalizePath($path),
            $this->normalizePath($path),
            $handler,
            $name
        );

        $this->storeRoute($routeDefinition);
    }

    /**
     * Stores a route definition for later matching.
     *
     * @param RouteDefinition $routeDefinition Route definition instance
     */
    protected function storeRoute(RouteDefinition $routeDefinition): void
    {
        // Routes will be managed by a route collection class (to be implemented)
        // For now, we register them in an array property
        if (!isset($this->routes)) {
            $this->routes = [];
        }

        $this->routes[] = $routeDefinition;
    }

    /**
     * Retrieves all registered route definitions.
     *
     * @return iterable All route definitions
     */
    public function getRoutes(): iterable
    {
        return $this->routes ?? [];
    }

    /**
     * Matches a request to registered routes.
     *
     * Returns null if no match is found.
     *
     * @param ServerRequestInterface $request PSR-7 request object
     * @return RouteDefinition|null Matched route or null
     */
    public function match(ServerRequestInterface $request): RouteDefinition|null
    {
        $method = strtoupper($request->getMethod());
        $path = $this->normalizePath($request->getUri()->getPath());

        foreach ($this->getRoutes() as $routeDefinition) {
            if (strtoupper($routeDefinition->getMethod()) === $method && $routeDefinition->getPath() === $path) {
                return $routeDefinition;
            }
        }

        return null;
    }

    /**
     * Matches a request and throws exception if no match found.
     *
     * @param ServerRequestInterface $request PSR-7 request object
     * @return RouteDefinition Matched route definition
     * @throws NotFoundRouteException If no route matches
     */
    public function matchOrFail(ServerRequestInterface $request): RouteDefinition
    {
        $method = strtoupper($request->getMethod());
        $path = $this->normalizePath($request->getUri()->getPath());

        foreach ($this->getRoutes() as $routeDefinition) {
            if (strtoupper($routeDefinition->getMethod()) === $method && $routeDefinition->getPath() === $path) {
                return $routeDefinition;
            }
        }

        throw new NotFoundRouteException('No route matched the request');
    }

    /**
     * Sets a fallback handler for unmatched requests.
     *
     * @param callable|string|null $handler Fallback handler
     * @return self Fluent interface
     */
    public function fallback(mixed $handler): self
    {
        $this->fallbackHandler = $handler;
        return $this;
    }

    /**
     * Generates a URL for a named route with parameters.
     *
     * @param string $name Route name
     * @param array $parameters Route parameters
     * @return string Generated URL path
     * @throws RouteNotFoundException If route name not found
     */
    public function url(string $name, array $parameters = []): string
    {
        $routes = $this->getRoutes();
        $foundRoute = null;

        foreach ($routes as $routeDefinition) {
            if ($routeDefinition->getName() === $name) {
                $foundRoute = $routeDefinition;
                break;
            }
        }

        if ($foundRoute === null) {
            throw new RouteNotFoundException(sprintf('Route "%s" not found for URL generation', $name));
        }

        $path = $foundRoute->getPath();
        $path = $this->replaceParameters($path, $parameters);

        return $path;
    }

    /**
     * Replaces {{param}} placeholders in a path.
     *
     * @param string $path Path string with placeholders
     * @param array $parameters Parameter values
     * @return string Path with parameters replaced
     */
    private function replaceParameters(string $path, array $parameters): string
    {
        foreach ($parameters as $key => $value) {
            $pattern = '{{' . $key . '}}';
            $path = str_replace($pattern, $value, $path);
        }

        return $path;
    }

    /**
     * Creates a new route group for nested routing.
     *
     * @param array $options Group options including prefix and middleware
     * @return RouteGroup Route group instance
     */
    public function group(array $options): RouteGroup
    {
        return new RouteGroup($options, $this);
    }

    /**
     * Auto-discovers and registers routes from modules.
     *
     * @param string|array $identifier Module identifier or array containing identifier and options
     * @return self Fluent interface
     * @throws MissingConfigurationException If modules_path not configured
     */
    public function module(string|array $identifier): self
    {
        $identifier = is_string($identifier) ? [$identifier] : $identifier;

        $modulesPath = $this->config->getModulesPath();
        $routesFile = $this->config->getRoutesFile();

        if ($modulesPath === null) {
            throw new MissingConfigurationException(
                'modules_path configuration is required to use module() method'
            );
        }

        $prefix = $identifier[0] ?? '';

        foreach ($modulesPath as $basePath) {
            $modulePath = $basePath . '/' . $prefix . '/' . $routesFile;

            if (file_exists($modulePath)) {
                $this->loadModuleRoutes($modulePath);
            }
        }

        return $this;
    }

    /**
     * Loads and registers routes from a module routes file.
     *
     * @param string $routesFile Path to routes.php file
     */
    private function loadModuleRoutes(string $routesFile): void
    {
        $moduleRoutes = require $routesFile;

        foreach ($moduleRoutes as $routeData) {
            if (!isset($routeData['method'], $routeData['path'], $routeData['handler'])) {
                continue;
            }

            $this->registerRoute(
                $routeData['method'],
                $routeData['path'],
                $routeData['handler'],
                $routeData['name'] ?? null
            );
        }
    }

    /**
     * Creates an iterator over registered routes.
     *
     * @return \Traversable Array iterator over route collection
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->routes ?? []);
    }
}