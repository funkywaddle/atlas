<?php

namespace Atlas\Router;

/**
 * Represents the result of a route matching operation.
 */
class MatchResult implements \JsonSerializable
{
    public function __construct(
        private readonly bool $found,
        private readonly RouteDefinition|null $route = null,
        private readonly array $parameters = [],
        private readonly array $diagnostics = []
    ) {}

    public function isFound(): bool
    {
        return $this->found;
    }

    public function getRoute(): ?RouteDefinition
    {
        return $this->route;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getDiagnostics(): array
    {
        return $this->diagnostics;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'found' => $this->found,
            'route' => $this->route,
            'parameters' => $this->parameters,
            'diagnostics' => $this->diagnostics
        ];
    }
}
