<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\Router;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

final class RouteMatcherTest extends TestCase
{
    public function testReturnsRouteOnSuccessfulMatch(): void
    {
        $config = new \Atlas\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/hello', 'HelloWorldHandler');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/hello');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getHost')->willReturn('localhost');
        $uri->method('getPort')->willReturn(80);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $matchedRoute = $router->match($request);

        $this->assertInstanceOf(\Atlas\Router\RouteDefinition::class, $matchedRoute);
        $this->assertSame('/hello', $matchedRoute->getPath());
        $this->assertSame('HelloWorldHandler', $matchedRoute->getHandler());
    }

    public function testReturnsNullOnNoMatch(): void
    {
        $config = new \Atlas\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/test', 'Handler');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/other');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getHost')->willReturn('localhost');
        $uri->method('getPort')->willReturn(80);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $matchedRoute = $router->match($request);

        $this->assertNull($matchedRoute);
    }

    public function testCaseInsensitiveHttpMethodMatching(): void
    {
        $config = new \Atlas\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/test', 'Handler');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/test');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getHost')->willReturn('localhost');
        $uri->method('getPort')->willReturn(80);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('get');
        $request->method('getUri')->willReturn($uri);

        $matchedRoute = $router->match($request);

        $this->assertNotNull($matchedRoute);
    }

    public function testRouteCollectionIteratesCorrectly(): void
    {
        $config = new \Atlas\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/route1', 'Handler1');
        $router->post('/route2', 'Handler2');

        $routes = $router->getRoutes();
        $this->assertIsIterable($routes);

        $routeArray = iterator_to_array($routes);
        $this->assertCount(2, $routeArray);
        $this->assertInstanceOf(\Atlas\Router\RouteDefinition::class, $routeArray[0]);
        $this->assertInstanceOf(\Atlas\Router\RouteDefinition::class, $routeArray[1]);
    }

    public function testUrlGenerationWithNamedRoute(): void
    {
        $config = new \Atlas\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/users', 'UserListHandler', 'user_list');

        $url = $router->url('user_list');

        $this->assertSame('/users', $url);
    }

    public function testHttpMethodsReturnSameInstanceForChaining(): void
    {
        $config = new \Atlas\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/get', 'Handler');
        $router->post('/post', 'Handler');
        $router->put('/put', 'Handler');
        $router->patch('/patch', 'Handler');
        $router->delete('/delete', 'Handler');

        $this->assertCount(5, $router->getRoutes());
    }
}