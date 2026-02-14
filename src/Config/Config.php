<?php

namespace Atlas\Config;

use ArrayAccess;
use IteratorAggregate;

/**
 * Provides configuration management for the Atlas routing engine.
 *
 * Implements ArrayAccess and IteratorAggregate for flexible configuration access
 * and iteration over configuration options.
 *
 * @implements ArrayAccess<mixed, mixed>
 * @implements IteratorAggregate<mixed, mixed>
 */
class Config implements ArrayAccess, IteratorAggregate
{
    /**
     * Constructs a new Config instance with the provided options.
     *
     * @param array $options Configuration array containing routing settings
     */
    public function __construct(
        private readonly array $options
    ) {}

    /**
     * Retrieves a configuration value by key.
     *
     * @param string $key The configuration key to retrieve
     * @param mixed $default Default value if key does not exist
     * @return mixed The configuration value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Checks if a configuration key exists.
     *
     * @param string $key The configuration key to check
     * @return bool True if the key exists, false otherwise
     */
    public function has(string $key): bool
    {
        return isset($this->options[$key]);
    }

    /**
     * Retrieves the module path(s) configuration.
     *
     * Returns a single string as an array or an array of strings.
     *
     * @return array|string|null Module path(s) or null if not configured
     */
    public function getModulesPath(): array|string|null
    {
        $modulesPath = $this->get('modules_path');

        if ($modulesPath === null) {
            return null;
        }

        return is_array($modulesPath) ? $modulesPath : [$modulesPath];
    }

    /**
     * Gets the default routes file name.
     *
     * @return string Default routes file name
     */
    public function getRoutesFile(): string
    {
        return $this->get('routes_file', 'routes.php');
    }

    /**
     * Retrieves the custom modules glob pattern.
     *
     * @return string|null Modules glob pattern or null
     */
    public function getModulesGlob(): string|null
    {
        return $this->get('modules_glob');
    }

    /**
     * Gets a normalized list of module paths.
     *
     * Ensures always returns an array, converting single string paths.
     *
     * @return array List of module paths
     */
    public function getModulesPathList(): array
    {
        $modulesPath = $this->getModulesPath();

        if ($modulesPath === null) {
            return [];
        }

        return is_array($modulesPath) ? $modulesPath : [$modulesPath];
    }

    /**
     * Converts the configuration to an array.
     *
     * @return array Configuration options as array
     */
    public function toArray(): array
    {
        return $this->options;
    }

    /**
     * Checks if a configuration key exists (ArrayAccess interface).
     *
     * @param mixed $offset The configuration key
     * @return bool True if offset exists
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->options[$offset]);
    }

    /**
     * Retrieves a configuration value by offset (ArrayAccess interface).
     *
     * @param mixed $offset The configuration key
     * @return mixed Configuration value or null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->options[$offset] ?? null;
    }

    /**
     * Sets a configuration value by offset (ArrayAccess interface).
     *
     * @param mixed $offset The configuration key
     * @param mixed $value The configuration value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->options[$offset] = $value;
    }

    /**
     * Unsets a configuration key (ArrayAccess interface).
     *
     * @param mixed $offset The configuration key to unset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->options[$offset]);
    }

    /**
     * Creates an iterator for the configuration options.
     *
     * @return Traversable Array iterator over configuration
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->options);
    }
}