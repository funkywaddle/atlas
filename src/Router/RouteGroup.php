<?php

namespace Atlas\Router;

/**
 * Manages groupings of routes for prefix and middleware organization.
 *
 * @implements \IteratorAggregate<array-key, mixed>
 */
class RouteGroup
{
    use PathHelper;

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
        return new self($options, $router);
    }

    public function get(string $path, mixed $handler, string|null $name = null): RouteDefinition
    {
        $fullPath = $this->buildFullPath($path);
        $middleware = $this->options['middleware'] ?? [];
        $validation = $this->options['validation'] ?? [];
        $defaults = $this->options['defaults'] ?? [];

        if ($this->router) {
            return $this->router->registerCustomRoute('GET', $fullPath, $handler, $name, $middleware, $validation, $defaults);
        }
        
        return new RouteDefinition('GET', $fullPath, $fullPath, $handler, $name, $middleware, $validation, $defaults);
    }

    public function post(string $path, mixed $handler, string|null $name = null): RouteDefinition
    {
        $fullPath = $this->buildFullPath($path);
        $middleware = $this->options['middleware'] ?? [];
        $validation = $this->options['validation'] ?? [];
        $defaults = $this->options['defaults'] ?? [];

        if ($this->router) {
            return $this->router->registerCustomRoute('POST', $fullPath, $handler, $name, $middleware, $validation, $defaults);
        }
        
        return new RouteDefinition('POST', $fullPath, $fullPath, $handler, $name, $middleware, $validation, $defaults);
    }

    public function put(string $path, mixed $handler, string|null $name = null): RouteDefinition
    {
        $fullPath = $this->buildFullPath($path);
        $middleware = $this->options['middleware'] ?? [];
        $validation = $this->options['validation'] ?? [];
        $defaults = $this->options['defaults'] ?? [];

        if ($this->router) {
            return $this->router->registerCustomRoute('PUT', $fullPath, $handler, $name, $middleware, $validation, $defaults);
        }
        
        return new RouteDefinition('PUT', $fullPath, $fullPath, $handler, $name, $middleware, $validation, $defaults);
    }

    public function patch(string $path, mixed $handler, string|null $name = null): RouteDefinition
    {
        $fullPath = $this->buildFullPath($path);
        $middleware = $this->options['middleware'] ?? [];
        $validation = $this->options['validation'] ?? [];
        $defaults = $this->options['defaults'] ?? [];

        if ($this->router) {
            return $this->router->registerCustomRoute('PATCH', $fullPath, $handler, $name, $middleware, $validation, $defaults);
        }
        
        return new RouteDefinition('PATCH', $fullPath, $fullPath, $handler, $name, $middleware, $validation, $defaults);
    }

    public function delete(string $path, mixed $handler, string|null $name = null): RouteDefinition
    {
        $fullPath = $this->buildFullPath($path);
        $middleware = $this->options['middleware'] ?? [];
        $validation = $this->options['validation'] ?? [];
        $defaults = $this->options['defaults'] ?? [];

        if ($this->router) {
            return $this->router->registerCustomRoute('DELETE', $fullPath, $handler, $name, $middleware, $validation, $defaults);
        }
        
        return new RouteDefinition('DELETE', $fullPath, $fullPath, $handler, $name, $middleware, $validation, $defaults);
    }

    public function redirect(string $path, string $destination, int $status = 302): RouteDefinition
    {
        $fullPath = $this->buildFullPath($path);
        $middleware = $this->options['middleware'] ?? [];
        $validation = $this->options['validation'] ?? [];
        $defaults = $this->options['defaults'] ?? [];

        if ($this->router) {
            return $this->router->registerCustomRoute('REDIRECT', $fullPath, $destination, null, $middleware, $validation, $defaults)->attr('status', $status);
        }

        return (new RouteDefinition('REDIRECT', $fullPath, $fullPath, $destination, null, $middleware, $validation, $defaults))->attr('status', $status);
    }

    public function fallback(mixed $handler): self
    {
        $this->options['fallback'] = $handler;
        
        $prefix = $this->options['prefix'] ?? '/';
        $middleware = $this->options['middleware'] ?? [];
        
        if ($this->router) {
            $this->router->registerCustomRoute('FALLBACK', $this->joinPaths($prefix, '/_fallback'), $handler, null, $middleware)
                 ->attr('_fallback', $handler)
                 ->attr('_fallback_prefix', $this->normalizePath($prefix));
        }

        return $this;
    }

    public function registerCustomRoute(string $method, string $path, mixed $handler, string|null $name = null, array $middleware = [], array $validation = [], array $defaults = []): RouteDefinition
    {
        $fullPath = $this->buildFullPath($path);
        $mergedMiddleware = array_merge($this->options['middleware'] ?? [], $middleware);
        
        $route = null;
        if ($this->router) {
            $route = $this->router->registerCustomRoute($method, $fullPath, $handler, $name, $mergedMiddleware);
        } else {
            $route = new RouteDefinition($method, $fullPath, $fullPath, $handler, $name, $mergedMiddleware);
        }

        // Apply group-level validation and defaults
        $route->valid($this->options['validation'] ?? []);
        foreach ($this->options['defaults'] ?? [] as $p => $v) {
            $route->default($p, $v);
        }

        // Apply route-level validation and defaults
        $route->valid($validation);
        foreach ($defaults as $p => $v) {
            $route->default($p, $v);
        }

        return $route;
    }

    /**
     * Builds the full path with group prefix.
     *
     * @param string $path Route path without prefix
     * @return string Complete path with prefix
     */
    private function buildFullPath(string $path): string
    {
        return $this->joinPaths($this->options['prefix'] ?? '', $path);
    }

    public function group(array|callable $options): RouteGroup
    {
        if (is_callable($options)) {
            $options($this);
            return $this;
        }

        $prefix = $this->options['prefix'] ?? '';
        $newPrefix = $this->joinPaths($prefix, $options['prefix'] ?? '');
        
        $middleware = $this->options['middleware'] ?? [];
        $newMiddleware = array_merge($middleware, $options['middleware'] ?? []);

        $validation = $this->options['validation'] ?? [];
        $newValidation = array_merge($validation, $options['validation'] ?? []);

        $defaults = $this->options['defaults'] ?? [];
        $newDefaults = array_merge($defaults, $options['defaults'] ?? []);
        
        $mergedOptions = array_merge($this->options, $options);
        $mergedOptions['prefix'] = $newPrefix;
        $mergedOptions['middleware'] = $newMiddleware;
        $mergedOptions['validation'] = $newValidation;
        $mergedOptions['defaults'] = $newDefaults;

        return new RouteGroup($mergedOptions, $this->router);
    }

    /**
     * Sets validation rules for parameters at the group level.
     *
     * @param array|string $param Parameter name or array of rules
     * @param array|string $rules Rules if first param is string
     * @return self
     */
    public function valid(array|string $param, array|string $rules = []): self
    {
        if (!isset($this->options['validation'])) {
            $this->options['validation'] = [];
        }

        if (is_array($param)) {
            foreach ($param as $p => $r) {
                $this->valid($p, $r);
            }
        } else {
            $this->options['validation'][$param] = is_string($rules) ? [$rules] : $rules;
        }

        return $this;
    }

    /**
     * Sets a default value for a parameter at the group level.
     *
     * @param string $param
     * @param mixed $value
     * @return self
     */
    public function default(string $param, mixed $value): self
    {
        if (!isset($this->options['defaults'])) {
            $this->options['defaults'] = [];
        }

        $this->options['defaults'][$param] = $value;

        return $this;
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
    public function module(string|array $identifier, string|null $prefix = null): self
    {
        if ($this->router) {
            // We need to pass the group context to the module loading.
            // But ModuleLoader uses the router directly.
            // If we use $this->router->module(), it won't have the group prefix/middleware.
            // We should probably allow ModuleLoader to take a "target" which can be a Router or RouteGroup.
            
            // For now, let's just use the router but we have a problem: inheritance.
            // A better way is to make RouteGroup have a way to load modules.
            
            $moduleLoader = new ModuleLoader($this->router->getConfig(), $this);
            $moduleLoader->load($identifier, $prefix);
        }
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @internal
     */
    public function getConfig(): Config
    {
        return $this->router->getConfig();
    }
}