<?php

namespace Atlas;

class RouteGroup
{
    public function __construct(
        private array $options = [],
        private readonly Router $router = null
    ) {}

    public static function create(array $options, Router $router): self
    {
        $self = new self($options);
        $self->router = $router;
        return $self;
    }

    public function get(string $path, string|callable $handler, string|null $name = null): self
    {
        $fullPath = $this->buildFullPath($path);
        return $this->router ? $this->router->get($fullPath, $handler, $name) : $this;
    }

    public function post(string $path, string|callable $handler, string|null $name = null): self
    {
        $fullPath = $this->buildFullPath($path);
        return $this->router ? $this->router->post($fullPath, $handler, $name) : $this;
    }

    public function put(string $path, string|callable $handler, string|null $name = null): self
    {
        $fullPath = $this->buildFullPath($path);
        return $this->router ? $this->router->put($fullPath, $handler, $name) : $this;
    }

    public function patch(string $path, string|callable $handler, string|null $name = null): self
    {
        $fullPath = $this->buildFullPath($path);
        return $this->router ? $this->router->patch($fullPath, $handler, $name) : $this;
    }

    public function delete(string $path, string|callable $handler, string|null $name = null): self
    {
        $fullPath = $this->buildFullPath($path);
        return $this->router ? $this->router->delete($fullPath, $handler, $name) : $this;
    }

    private function buildFullPath(string $path): string
    {
        $prefix = $this->options['prefix'] ?? '';

        if (empty($prefix)) {
            return $path;
        }

        return rtrim($prefix, '/') . '/' . ltrim($path, '/');
    }

    public function setOption(string $key, mixed $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}