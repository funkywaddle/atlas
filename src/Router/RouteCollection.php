<?php

namespace Atlas\Router;

/**
 * Manages the storage and retrieval of route definitions.
 *
 * @implements \IteratorAggregate<int, RouteDefinition>
 */
class RouteCollection implements \IteratorAggregate, \Serializable
{
    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    public function unserialize(string $data): void
    {
        $this->__unserialize(unserialize($data));
    }

    public function __serialize(): array
    {
        return [
            'routes' => $this->routes,
            'namedRoutes' => $this->namedRoutes,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->routes = $data['routes'];
        $this->namedRoutes = $data['namedRoutes'];
    }
    /**
     * @var RouteDefinition[]
     */
    private array $routes = [];

    /**
     * @var array<string, RouteDefinition>
     */
    private array $namedRoutes = [];

    /**
     * Adds a route definition to the collection.
     *
     * @param RouteDefinition $route The route to add
     * @return void
     */
    public function add(RouteDefinition $route): void
    {
        $this->routes[] = $route;

        if ($name = $route->getName()) {
            $this->namedRoutes[$name] = $route;
        }
    }

    /**
     * Finds a route by its name.
     *
     * @param string $name The name of the route
     * @return RouteDefinition|null The route if found, null otherwise
     */
    public function getByName(string $name): ?RouteDefinition
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * Returns all route definitions in the collection.
     *
     * @return RouteDefinition[]
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * Returns an iterator for the route definitions.
     *
     * @return \Traversable<int, RouteDefinition>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->routes);
    }

    /**
     * Gets the number of routes in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->routes);
    }
}
