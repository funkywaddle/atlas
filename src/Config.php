<?php

namespace Atlas;

use ArrayAccess;
use IteratorAggregate;

class Config implements ArrayAccess, IteratorAggregate
{
    public function __construct(
        private readonly array $options
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->options[$key]);
    }

    public function getModulesPath(): array|string|null
    {
        $modulesPath = $this->get('modules_path');

        if ($modulesPath === null) {
            return null;
        }

        return is_array($modulesPath) ? $modulesPath : [$modulesPath];
    }

    public function getRoutesFile(): string
    {
        return $this->get('routes_file', 'routes.php');
    }

    public function getModulesGlob(): string|null
    {
        return $this->get('modules_glob');
    }

    public function getModulesPathList(): array
    {
        $modulesPath = $this->getModulesPath();

        if ($modulesPath === null) {
            return [];
        }

        return is_array($modulesPath) ? $modulesPath : [$modulesPath];
    }

    public function toArray(): array
    {
        return $this->options;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->options[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->options[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->options[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->options[$offset]);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->options);
    }
}