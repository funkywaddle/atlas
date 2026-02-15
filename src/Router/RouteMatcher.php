<?php

namespace Atlas\Router;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles the logic for matching a request to a route.
 */
class RouteMatcher
{
    use PathHelper;

    private array $compiledPatterns = [];

    /**
     * Matches a request against a collection of routes.
     *
     * @param ServerRequestInterface $request The request to match
     * @param RouteCollection $routes The collection of routes to match against
     * @return RouteDefinition|null The matched route or null if no match found
     */
    public function match(ServerRequestInterface $request, RouteCollection $routes): ?RouteDefinition
    {
        $method = strtoupper($request->getMethod());
        $path = $this->normalizePath($request->getUri()->getPath());
        $host = $request->getUri()->getHost();

        $routesToMatch = $routes;

        foreach ($routesToMatch as $route) {
            $attributes = [];
            if ($this->isMatch($method, $path, $host, $route, $attributes)) {
                $attributes = $this->mergeDefaults($route, $attributes);
                return $this->applyAttributes($route, $attributes);
            }

            // i18n support: check alternative paths
            $routeAttributes = $route->getAttributes();
            if (isset($routeAttributes['i18n']) && is_array($routeAttributes['i18n'])) {
                foreach ($routeAttributes['i18n'] as $lang => $i18nPath) {
                    $normalizedI18nPath = $this->normalizePath($i18nPath);
                    if ($this->isMatch($method, $path, $host, $route, $attributes, $normalizedI18nPath)) {
                        $attributes['lang'] = $lang;
                        $attributes = $this->mergeDefaults($route, $attributes);
                        return $this->applyAttributes($route, $attributes);
                    }
                }
            }
        }

        // Try to find a fallback
        return $this->matchFallback($path, $routes);
    }

    /**
     * Attempts to match a fallback handler for the given path.
     *
     * @param string $path
     * @param RouteCollection $routes
     * @return RouteDefinition|null
     */
    private function matchFallback(string $path, RouteCollection $routes): ?RouteDefinition
    {
        $bestFallback = null;
        $longestPrefix = -1;

        foreach ($routes as $route) {
            $attributes = $route->getAttributes();
            if (isset($attributes['_fallback'])) {
                $prefix = $attributes['_fallback_prefix'] ?? '';
                if (str_starts_with($path, $prefix) && strlen($prefix) > $longestPrefix) {
                    $longestPrefix = strlen($prefix);
                    $bestFallback = $route;
                }
            }
        }

        if ($bestFallback) {
            $attributes = $bestFallback->getAttributes();
            return new RouteDefinition(
                'FALLBACK',
                $path,
                $path,
                $attributes['_fallback'],
                null,
                $bestFallback->getMiddleware()
            );
        }

        return null;
    }

    /**
     * Merges default values for missing optional parameters.
     *
     * @param RouteDefinition $route
     * @param array $attributes
     * @return array
     */
    private function mergeDefaults(RouteDefinition $route, array $attributes): array
    {
        return array_merge($route->getDefaults(), $attributes);
    }

    /**
     * Determines if a request matches a route definition.
     *
     * @param string $method The request method
     * @param string $path The request path
     * @param RouteDefinition $route The route to check
     * @param array $attributes Extracted attributes
     * @return bool
     */
    private function isMatch(
        string $method,
        string $path,
        string $host,
        RouteDefinition $route,
        array &$attributes,
        ?string $overridePath = null
    ): bool {
        $routeMethod = strtoupper($route->getMethod());
        if ($routeMethod !== $method && $routeMethod !== 'REDIRECT') {
            return false;
        }

        // Subdomain constraint check
        $routeAttributes = $route->getAttributes();
        if (isset($routeAttributes['subdomain'])) {
            $subdomain = $routeAttributes['subdomain'];
            if (!str_starts_with($host, $subdomain . '.')) {
                return false;
            }
        }

        $pattern = $overridePath
            ? $this->compilePatternFromPath($overridePath, $route)
            : $this->getPatternForRoute($route);

        if (preg_match($pattern, $path, $matches)) {
            $attributes = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }

    /**
     * @internal
     */
    public function getPatternForRoute(RouteDefinition $route): string
    {
        return $this->compilePattern($route);
    }

    /**
     * Compiles a route path into a regex pattern.
     *
     * @param RouteDefinition $route
     * @return string
     */
    private function compilePattern(RouteDefinition $route): string
    {
        $id = spl_object_id($route);
        if (isset($this->compiledPatterns[$id])) {
            return $this->compiledPatterns[$id];
        }

        $this->compiledPatterns[$id] = $this->compilePatternFromPath($route->getPath(), $route);
        return $this->compiledPatterns[$id];
    }

    /**
     * Compiles a specific path into a regex pattern using route's validation and defaults.
     *
     * @param string $path
     * @param RouteDefinition $route
     * @return string
     */
    private function compilePatternFromPath(string $path, RouteDefinition $route): string
    {
        $validation = $route->getValidation();
        $defaults = $route->getDefaults();

        $pattern = preg_replace_callback(
            '#/\{\{([a-zA-Z0-9_]+)(\?)?\}\}#',
            function ($matches) use ($validation, $defaults) {
                $name = $matches[1];
                $optional = (isset($matches[2]) && $matches[2] === '?') || array_key_exists($name, $defaults);

                $rules = $validation[$name] ?? [];
                $regex = '[^/]+';

                // Validation rules support
                foreach ((array)$rules as $rule) {
                    if ($rule === 'numeric' || $rule === 'int') {
                        $regex = '[0-9]+';
                    } elseif ($rule === 'alpha') {
                        $regex = '[a-zA-Z]+';
                    } elseif ($rule === 'alphanumeric') {
                        $regex = '[a-zA-Z0-9]+';
                    } elseif (str_starts_with($rule, 'regex:')) {
                        $regex = substr($rule, 6);
                    }
                }

                if ($optional) {
                    return '(?:/(?P<' . $name . '>' . $regex . '))?';
                }

                return '/(?P<' . $name . '>' . $regex . ')';
            },
            $path
        );

        $pattern = str_replace('//', '/', $pattern);

        return '#^' . $pattern . '/?$#';
    }

    /**
     * Applies extracted attributes to a new route definition.
     *
     * @param RouteDefinition $route
     * @param array $attributes
     * @return RouteDefinition
     */
    private function applyAttributes(
        RouteDefinition $route,
        array $attributes
    ): RouteDefinition {
        $data = $route->toArray();
        $data['attributes'] = array_merge($data['attributes'], $attributes);

        return new RouteDefinition(
            $data['method'],
            $data['pattern'],
            $data['path'],
            $data['handler'],
            $data['name'],
            $data['middleware'],
            $data['validation'],
            $data['defaults'],
            $data['module'],
            $data['attributes']
        );
    }
}
