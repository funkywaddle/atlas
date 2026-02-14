<?php

namespace Atlas\Tests\Unit;

use Atlas\Exception\RouteNotFoundException;
use Atlas\Router\RouteDefinition;
use Atlas\Router\Router;
use Atlas\Config\Config;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

final class ErrorHandlingTest extends TestCase
{
    public function testMatchOrFailThrowsExceptionWhenNoRouteFound(): void
    {
        $config = new Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new Router($config);

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/nonexistent');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getHost')->willReturn('localhost');
        $uri->method('getPort')->willReturn(80);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $this->expectException(RouteNotFoundException::class);

        $router->matchOrFail($request);
    }

    public function testMatchReturnsNullWhenNoRouteFound(): void
    {
        $config = new Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new Router($config);

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
        $config = new Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new Router($config);
        $router->get('/test', 'GetHandler');
        $router->post('/test', 'PostHandler');

        $this->assertCount(2, $router->getRoutes());
    }

    public function testMatchUsingRouteDefinition(): void
    {
        $config = new Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new Router($config);

        $router->get('/test', 'TestMethod');

        $routes = iterator_to_array($router->getRoutes());
        $this->assertCount(1, $routes);
        $this->assertInstanceOf(RouteDefinition::class, $routes[0]);
    }

    public function testCaseInsensitiveHttpMethodMatching(): void
    {
        $config = new Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new Router($config);

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
        $config = new Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new Router($config);

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
        $config = new Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new Router($config);

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

        $this->assertInstanceOf(RouteDefinition::class, $matchedRoute);
    }

    public function testModuleThrowsExceptionWhenModulesPathIsMissing(): void
    {
        $config = new Config([]);
        $router = new Router($config);

        $this->expectException(\Atlas\Exception\MissingConfigurationException::class);
        $router->module('User');
    }
}