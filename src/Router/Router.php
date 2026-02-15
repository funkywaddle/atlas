<?php

namespace Atlas\Router;

use Psr\Http\Message\ServerRequestInterface;
use Atlas\Config\Config;
use Atlas\Exception\RouteNotFoundException;
use Atlas\Exception\MissingConfigurationException;

class Router
{
    use PathHelper;

    private RouteCollection $routes;
    private readonly RouteMatcher $matcher;
    private readonly ModuleLoader $loader;
    protected mixed $fallbackHandler = null;

    public function __construct(
        private readonly Config $config
    ) {
        $this->routes = new RouteCollection();
        $this->matcher = new RouteMatcher();
        $this->loader = new ModuleLoader($this->config, $this);
    }

    public function get(string $path, string|callable $handler, string|null $name = null): RouteDefinition
    {
        return $this->registerRoute('GET', $path, $handler, $name);
    }

    public function post(string $path, string|callable $handler, string|null $name = null): RouteDefinition
    {
        return $this->registerRoute('POST', $path, $handler, $name);
    }

    public function put(string $path, string|callable $handler, string|null $name = null): RouteDefinition
    {
        return $this->registerRoute('PUT', $path, $handler, $name);
    }

    public function patch(string $path, string|callable $handler, string|null $name = null): RouteDefinition
    {
        return $this->registerRoute('PATCH', $path, $handler, $name);
    }

    public function delete(string $path, string|callable $handler, string|null $name = null): RouteDefinition
    {
        return $this->registerRoute('DELETE', $path, $handler, $name);
    }

    public function registerCustomRoute(string $method, string $path, mixed $handler, string|null $name = null, array $middleware = [], array $validation = [], array $defaults = []): RouteDefinition
    {
        return $this->registerRoute($method, $path, $handler, $name, $middleware, $validation, $defaults);
    }

    private function registerRoute(string $method, string $path, mixed $handler, string|null $name = null, array $middleware = [], array $validation = [], array $defaults = []): RouteDefinition
    {
        $normalizedPath = $this->normalizePath($path);
        $routeDefinition = new RouteDefinition(
            $method,
            $normalizedPath,
            $normalizedPath,
            $handler,
            $name,
            $middleware,
            $validation,
            $defaults
        );

        $this->storeRoute($routeDefinition);

        return $routeDefinition;
    }

    protected function storeRoute(RouteDefinition $routeDefinition): void
    {
        $this->routes->add($routeDefinition);
    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    public function setRoutes(RouteCollection $routes): self
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * @internal
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    public function match(ServerRequestInterface $request): RouteDefinition|null
    {
        return $this->matcher->match($request, $this->routes);
    }

    public function inspect(ServerRequestInterface $request): MatchResult
    {
        $method = strtoupper($request->getMethod());
        $path = $this->normalizePath($request->getUri()->getPath());
        $host = $request->getUri()->getHost();

        $diagnostics = [
            'method' => $method,
            'path' => $path,
            'host' => $host,
            'attempts' => []
        ];

        foreach ($this->routes as $route) {
            $attributes = [];
            $routeMethod = strtoupper($route->getMethod());
            $routePath = $route->getPath();

            $matchStatus = 'mismatch';
            if ($routeMethod !== $method && $routeMethod !== 'REDIRECT') {
                $matchStatus = 'method_mismatch';
            } else {
                $pattern = $this->matcher->getPatternForRoute($route);
                if (preg_match($pattern, $path, $matches)) {
                    $matchStatus = 'matched';
                    $attributes = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    $attributes = array_merge($route->getDefaults(), $attributes);

                    return new MatchResult(true, $route, $attributes, $diagnostics);
                }
            }

            $diagnostics['attempts'][] = [
                'route' => $route->getName() ?? $route->getMethod() . ' ' . $route->getPath(),
                'status' => $matchStatus,
                'pattern' => $this->matcher->getPatternForRoute($route)
            ];
        }

        return new MatchResult(false, null, [], $diagnostics);
    }

    public function matchOrFail(ServerRequestInterface $request): RouteDefinition
    {
        $route = $this->match($request);

        if ($route === null) {
            throw new RouteNotFoundException('No route matched the request');
        }

        return $route;
    }

    public function redirect(string $path, string $destination, int $status = 302): RouteDefinition
    {
        return $this->registerRoute('REDIRECT', $path, $destination)->attr('status', $status);
    }

    public function fallback(mixed $handler): self
    {
        $this->fallbackHandler = $handler;

        // Register a special route to carry the global fallback
        $this->registerRoute('FALLBACK', '/_fallback', $handler)
             ->attr('_fallback', $handler)
             ->attr('_fallback_prefix', '/');

        return $this;
    }

    public function getFallbackHandler(): mixed
    {
        return $this->fallbackHandler;
    }

    public function url(string $name, array $parameters = []): string
    {
        $foundRoute = $this->routes->getByName($name);

        if ($foundRoute === null) {
            throw new RouteNotFoundException(sprintf('Route "%s" not found for URL generation', $name));
        }

        $path = $foundRoute->getPath();
        $path = $this->replaceParameters($path, $parameters, $foundRoute->getDefaults());

        return $path;
    }

    private function replaceParameters(string $path, array $parameters, array $defaults = []): string
    {
        if (!str_contains($path, '{{')) {
            return $this->normalizePath($path);
        }

        preg_match_all('/\{\{([a-zA-Z0-9_]+)(\?)?\}\}/', $path, $matches);
        $parameterNames = $matches[1];
        $isOptional = $matches[2];

        foreach ($parameterNames as $index => $name) {
            $pattern = '{{' . $name . ($isOptional[$index] === '?' ? '?' : '') . '}}';

            if (array_key_exists($name, $parameters)) {
                $path = str_replace($pattern, (string)$parameters[$name], $path);
            } elseif (array_key_exists($name, $defaults)) {
                $path = str_replace($pattern, (string)$defaults[$name], $path);
            } elseif ($isOptional[$index] === '?') {
                $path = str_replace($pattern, '', $path);
            } else {
                throw new \InvalidArgumentException(sprintf('Missing required parameter "%s" for route URL generation', $name));
            }
        }

        return $this->normalizePath($path);
    }

    public function group(array $options): RouteGroup
    {
        return new RouteGroup($options, $this);
    }

    public function module(string|array $identifier, string|null $prefix = null): self
    {
        $this->loader->load($identifier, $prefix);
        return $this;
    }
}
