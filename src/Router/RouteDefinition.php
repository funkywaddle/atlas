<?php

namespace Atlas\Router;

/**
 * Represents a complete route definition with matching patterns, handlers, and metadata.
 */
class RouteDefinition implements \JsonSerializable, \Serializable
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
            'method' => $this->method,
            'pattern' => $this->pattern,
            'path' => $this->path,
            'handler' => $this->handler,
            'name' => $this->name,
            'middleware' => $this->middleware,
            'validation' => $this->validation,
            'defaults' => $this->defaults,
            'module' => $this->module,
            'attributes' => $this->attributes,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->method = $data['method'];
        $this->pattern = $data['pattern'];
        $this->path = $data['path'];
        $this->handler = $data['handler'];
        $this->name = $data['name'];
        $this->middleware = $data['middleware'];
        $this->validation = $data['validation'];
        $this->defaults = $data['defaults'];
        $this->module = $data['module'];
        $this->attributes = $data['attributes'];
    }

    /**
     * @internal
     */
    public function setModule(?string $module): void
    {
        $this->module = $module;
    }
    public function __construct(
        private string $method,
        private string $pattern,
        private string $path,
        private mixed $handler,
        private string|null $name = null,
        private array $middleware = [],
        private array $validation = [],
        private array $defaults = [],
        private string|null $module = null,
        private array $attributes = []
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }
    public function getPattern(): string
    {
        return $this->pattern;
    }
    public function getPath(): string
    {
        return $this->path;
    }
    public function getHandler(): mixed
    {
        return $this->handler;
    }
    public function getName(): ?string
    {
        return $this->name;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function middleware(string|array $middleware): self
    {
        if (is_string($middleware)) {
            $this->middleware[] = $middleware;
        } else {
            $this->middleware = array_merge($this->middleware, $middleware);
        }
        return $this;
    }

    public function getValidation(): array
    {
        return $this->validation;
    }

    public function valid(array|string $param, array|string $rules = []): self
    {
        if (is_array($param)) {
            foreach ($param as $p => $r) {
                $this->valid($p, $r);
            }
        } else {
            $this->validation[$param] = is_string($rules) ? [$rules] : $rules;
        }
        return $this;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function default(string $param, mixed $value): self
    {
        $this->defaults[$param] = $value;
        return $this;
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function attr(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function meta(array $data): self
    {
        $this->attributes = array_merge($this->attributes, $data);
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'pattern' => $this->pattern,
            'path' => $this->path,
            'handler' => is_callable($this->handler) ? 'Closure' : $this->handler,
            'name' => $this->name,
            'middleware' => $this->middleware,
            'validation' => $this->validation,
            'defaults' => $this->defaults,
            'module' => $this->module,
            'attributes' => $this->attributes
        ];
    }
}
