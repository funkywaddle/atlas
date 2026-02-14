<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\Router;
use Atlas\Config\Config;
use Atlas\Router\MatchResult;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class InspectorApiTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $config = new Config(['modules_path' => ['/path/to/modules']]);
        $this->router = new Router($config);
    }

    public function testInspectFindsMatch(): void
    {
        $this->router->get('/users/{{id}}', 'handler', 'user_detail');
        
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/users/42');
        $uri->method('getHost')->willReturn('localhost');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $result = $this->router->inspect($request);

        $this->assertInstanceOf(MatchResult::class, $result);
        $this->assertTrue($result->isFound());
        $this->assertSame('user_detail', $result->getRoute()->getName());
        $this->assertSame(['id' => '42'], $result->getParameters());
    }

    public function testInspectReturnsDiagnosticsOnMismatch(): void
    {
        $this->router->get('/users/{{id}}', 'handler', 'user_detail')->valid('id', 'numeric');
        
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/users/abc');
        $uri->method('getHost')->willReturn('localhost');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $result = $this->router->inspect($request);

        $this->assertFalse($result->isFound());
        $diagnostics = $result->getDiagnostics();
        $this->assertArrayHasKey('attempts', $diagnostics);
        $this->assertCount(1, $diagnostics['attempts']);
        $this->assertSame('user_detail', $diagnostics['attempts'][0]['route']);
        $this->assertSame('mismatch', $diagnostics['attempts'][0]['status']);
    }

    public function testRouteDefinitionIsJsonSerializable(): void
    {
        $route = $this->router->get('/test', 'handler', 'test_route');
        $json = json_encode($route);
        $data = json_decode($json, true);

        $this->assertSame('GET', $data['method']);
        $this->assertSame('/test', $data['path']);
        $this->assertSame('test_route', $data['name']);
        $this->assertSame('handler', $data['handler']);
    }

    public function testMatchResultIsJsonSerializable(): void
    {
        $this->router->get('/test', 'handler', 'test_route');
        
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/test');
        $uri->method('getHost')->willReturn('localhost');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $result = $this->router->inspect($request);
        $json = json_encode($result);
        $data = json_decode($json, true);

        $this->assertTrue($data['found']);
        $this->assertSame('test_route', $data['route']['name']);
    }
}
