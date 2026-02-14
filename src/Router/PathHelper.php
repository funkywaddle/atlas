<?php

namespace Atlas\Router;

/**
 * Helper trait for path normalization across routing components.
 */
trait PathHelper
{
    /**
     * Normalizes a URI path by ensuring it has a leading slash and no trailing slash.
     *
     * @param string $path The path to normalize
     * @return string The normalized path
     */
    protected function normalizePath(string $path): string
    {
        $normalized = trim($path, '/');

        if (empty($normalized)) {
            return '/';
        }

        return '/' . $normalized;
    }

    /**
     * Joins two path segments ensuring proper slash separation.
     *
     * @param string $prefix The path prefix
     * @param string $path The path suffix
     * @return string The joined and normalized path
     */
    protected function joinPaths(string $prefix, string $path): string
    {
        $prefix = rtrim($prefix, '/');
        $path = ltrim($path, '/');

        if (empty($prefix)) {
            return $this->normalizePath($path);
        }

        return $this->normalizePath($prefix . '/' . $path);
    }
}
