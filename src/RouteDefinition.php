<?php

namespace Atlas;

use Psr\Http\Message\UriInterface;

final class RouteDefinition
{
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

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHandler(): string|callable
    {
        return $this->handler;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getValidation(): array
    {
        return $this->validation;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

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