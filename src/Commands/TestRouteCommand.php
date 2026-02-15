<?php

namespace Atlas\Commands;

use Atlas\Router\Router;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Command to test a specific request against the routing table.
 */
class TestRouteCommand
{
    /**
     * Executes the command.
     *
     * @param Router $router The router instance
     * @param array $argv CLI arguments
     * @return void
     */
    public function execute(Router $router, array $argv): void
    {
        $method = $argv[2] ?? 'GET';
        $path = $argv[3] ?? '/';
        $host = 'localhost'; // Default

        // PSR-7 mock request
        $uri = new class ($path, $host) implements UriInterface {
            public function __construct(private $path, private $host)
            {
            }
            public function getScheme(): string
            {
                return 'http';
            }
            public function getAuthority(): string
            {
                return $this->host;
            }
            public function getUserInfo(): string
            {
                return '';
            }
            public function getHost(): string
            {
                return $this->host;
            }
            public function getPort(): ?int
            {
                return null;
            }
            public function getPath(): string
            {
                return $this->path;
            }
            public function getQuery(): string
            {
                return '';
            }
            public function getFragment(): string
            {
                return '';
            }
            public function withScheme($scheme): UriInterface
            {
                return $this;
            }
            public function withUserInfo($user, $password = null): UriInterface
            {
                return $this;
            }
            public function withHost($host): UriInterface
            {
                return $this;
            }
            public function withPort($port): UriInterface
            {
                return $this;
            }
            public function withPath($path): UriInterface
            {
                return $this;
            }
            public function withQuery($query): UriInterface
            {
                return $this;
            }
            public function withFragment($fragment): UriInterface
            {
                return $this;
            }
            public function __toString(): string
            {
                return "http://{$this->host}{$this->path}";
            }
        };

        $request = new class ($method, $uri) implements ServerRequestInterface {
            public function __construct(private $method, private $uri)
            {
            }
            public function getProtocolVersion(): string
            {
                return '1.1';
            }
            public function withProtocolVersion($version): ServerRequestInterface
            {
                return $this;
            }
            public function getHeaders(): array
            {
                return [];
            }
            public function hasHeader($name): bool
            {
                return false;
            }
            public function getHeader($name): array
            {
                return [];
            }
            public function getHeaderLine($name): string
            {
                return '';
            }
            public function withHeader($name, $value): ServerRequestInterface
            {
                return $this;
            }
            public function withAddedHeader($name, $value): ServerRequestInterface
            {
                return $this;
            }
            public function withoutHeader($name): ServerRequestInterface
            {
                return $this;
            }
            public function getBody(): \Psr\Http\Message\StreamInterface
            {
                return $this->createMockStream();
            }
            public function withBody(\Psr\Http\Message\StreamInterface $body): ServerRequestInterface
            {
                return $this;
            }
            public function getRequestTarget(): string
            {
                return $this->uri->getPath();
            }
            public function withRequestTarget($requestTarget): ServerRequestInterface
            {
                return $this;
            }
            public function getMethod(): string
            {
                return $this->method;
            }
            public function withMethod($method): ServerRequestInterface
            {
                return $this;
            }
            public function getUri(): UriInterface
            {
                return $this->uri;
            }
            public function withUri(UriInterface $uri, $preserveHost = false): ServerRequestInterface
            {
                return $this;
            }
            public function getServerParams(): array
            {
                return [];
            }
            public function getCookieParams(): array
            {
                return [];
            }
            public function withCookieParams(array $cookies): ServerRequestInterface
            {
                return $this;
            }
            public function getQueryParams(): array
            {
                return [];
            }
            public function withQueryParams(array $query): ServerRequestInterface
            {
                return $this;
            }
            public function getUploadedFiles(): array
            {
                return [];
            }
            public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
            {
                return $this;
            }
            public function getParsedBody(): null|array|object
            {
                return null;
            }
            public function withParsedBody($data): ServerRequestInterface
            {
                return $this;
            }
            public function getAttributes(): array
            {
                return [];
            }
            public function getAttribute($name, $default = null): mixed
            {
                return $default;
            }
            public function withAttribute($name, $value): ServerRequestInterface
            {
                return $this;
            }
            public function withoutAttribute($name): ServerRequestInterface
            {
                return $this;
            }

            private function createMockStream()
            {
                return new class implements \Psr\Http\Message\StreamInterface {
                    public function __toString(): string
                    {
                        return '';
                    }
                    public function close(): void
                    {
                    }
                    public function detach()
                    {
                        return null;
                    }
                    public function getSize(): ?int
                    {
                        return 0;
                    }
                    public function tell(): int
                    {
                        return 0;
                    }
                    public function eof(): bool
                    {
                        return true;
                    }
                    public function isSeekable(): bool
                    {
                        return false;
                    }
                    public function seek($offset, $whence = SEEK_SET): void
                    {
                    }
                    public function rewind(): void
                    {
                    }
                    public function isWritable(): bool
                    {
                        return false;
                    }
                    public function write($string): int
                    {
                        return 0;
                    }
                    public function isReadable(): bool
                    {
                        return true;
                    }
                    public function read($length): string
                    {
                        return '';
                    }
                    public function getContents(): string
                    {
                        return '';
                    }
                    public function getMetadata($key = null)
                    {
                        return $key ? null : [];
                    }
                };
            }
        };

        $result = $router->inspect($request);

        if ($result->isFound()) {
            echo "Match Found!" . PHP_EOL;
            echo "Route: " . $result->getRoute()->getName() . " [" . $result->getRoute()->getMethod() . " " . $result->getRoute()->getPath() . "]" . PHP_EOL;
            echo "Parameters: " . json_encode($result->getParameters()) . PHP_EOL;
            exit(0);
        } else {
            echo "No Match Found." . PHP_EOL;
            if (in_array('--verbose', $argv)) {
                echo "Diagnostics:" . PHP_EOL;
                foreach ($result->getDiagnostics()['attempts'] as $attempt) {
                    echo "  - {$attempt['route']}: {$attempt['status']} (Pattern: {$attempt['pattern']})" . PHP_EOL;
                }
            }
            exit(2);
        }
    }
}
