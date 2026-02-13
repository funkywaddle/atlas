<?php

namespace Atlas;

use Psr\Http\Message\ServerRequestInterface;

class Router
{
    private array $routes = [];

    public function __construct(
        private readonly Config $config
    ) {}

    public function get(string $path, string|callable $handler, string|null $name = null): self
    {
        $this->registerRoute('GET', $path, $handler, $name);
        return $this;
    }

    public function post(string $path, string|callable $handler, string|null $name = null): self
    {
        $this->registerRoute('POST', $path, $handler, $name);
        return $this;
    }

    public function put(string $path, string|callable $handler, string|null $name = null): self
    {
        $this->registerRoute('PUT', $path, $handler, $name);
        return $this;
    }

    public function patch(string $path, string|callable $handler, string|null $name = null): self
    {
        $this->registerRoute('PATCH', $path, $handler, $name);
        return $this;
    }

    public function delete(string $path, string|callable $handler, string|null $name = null): self
    {
        $this->registerRoute('DELETE', $path, $handler, $name);
        return $this;
    }

    private function registerRoute(string $method, string $path, mixed $handler, string|null $name = null): void
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

    private function normalizePath(string $path): string
    {
        return '/' . ltrim($path, '/');
    }

    protected function storeRoute(RouteDefinition $routeDefinition): void
    {
        // Routes will be managed by a route collection class (to be implemented)
        // For now, we register them in an array property
        if (!isset($this->routes)) {
            $this->routes = [];
        }

        $this->routes[] = $routeDefinition;
    }

    public function getRoutes(): iterable
    {
        return $this->routes ?? [];
    }

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

    private function replaceParameters(string $path, array $parameters): string
    {
        foreach ($parameters as $key => $value) {
            $pattern = '{{' . $key . '}}';
            $path = str_replace($pattern, $value, $path);
        }

        return $path;
    }

    public function group(array $options): RouteGroup
    {
        return new RouteGroup($options, $this);
    }

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
}