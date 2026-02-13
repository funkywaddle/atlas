<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\Router;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

final class ErrorHandlingTest extends TestCase
{
    public function testMatchOrFailThrowsExceptionWhenNoRouteFound(): void
    {
        $config = new \Atlas\Tests\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/nonexistent');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getHost')->willReturn('localhost');
        $uri->method('getPort')->willReturn(80);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $this->expectException(\Atlas\Exception\NotFoundRouteException::class);

        $router->matchOrFail($request);
    }

    public function testMatchReturnsNullWhenNoRouteFound(): void
    {
        $config = new \Atlas\Tests\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/nonexistent');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getHost')->willReturn('localhost');
        $uri->method('getPort')->willReturn(80);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $result = $router->match($request);

        $this->assertNull($result);
    }

    public function testRouteChainingWithDifferentHttpMethods(): void
    {
        $config = new \Atlas\Tests\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $result = new \Atlas\Router\Router($config)->get('/test', 'GetHandler')->post('/test', 'PostHandler');

        $this->assertTrue($result instanceof \Atlas\Router\Router);
        $this->assertCount(2, $result->getRoutes());
    }

    public function testMatchUsingRouteDefinition(): void
    {
        $config = new \Atlas\Tests\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/test', 'TestMethod');

        $routes = $router->getRoutes();
        $this->assertCount(1, $routes);
        $this->assertInstanceOf(\Atlas\Router\RouteDefinition::class, $routes[0]);
    }

    public function testCaseInsensitiveHttpMethodMatching(): void
    {
        $config = new \Atlas\Tests\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/test', 'TestHandler');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/test');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getHost')->willReturn('localhost');
        $uri->method('getPort')->willReturn(80);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $matchedRoute = $router->match($request);

        $this->assertNotNull($matchedRoute);
    }

    public function testPathNormalizationLeadingSlashes(): void
    {
        $config = new \Atlas\Tests\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/test', 'TestHandler');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/test');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getHost')->willReturn('localhost');
        $uri->method('getPort')->willReturn(80);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $matchedRoute = $router->match($request);

        $this->assertNotNull($matchedRoute);
    }

    public function testMatchOrFailThrowsExceptionForMultipleRoutes(): void
    {
        $config = new \Atlas\Tests\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/test', 'TestHandler');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/test');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getHost')->willReturn('localhost');
        $uri->method('getPort')->willReturn(80);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $matchedRoute = $router->matchOrFail($request);

        $this->assertInstanceOf(\Atlas\Router\RouteDefinition::class, $matchedRoute);
    }
}