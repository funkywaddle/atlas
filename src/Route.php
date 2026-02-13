<?php

namespace Atlas;

use Psr\Http\Message\ServerRequestInterface;

final class Route
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly string|callable $handler
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
}