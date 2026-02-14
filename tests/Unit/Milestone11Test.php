<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\Router;
use Atlas\Config\Config;
use Atlas\Exception\RouteNotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class Milestone11Test extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $config = new Config(['modules_path' => ['/path/to/modules']]);
        $this->router = new Router($config);
    }

    public function testRedirectSupport(): void
    {
        $this->router->redirect('/old', '/new', 301);
        
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/old');
        $uri->method('getHost')->willReturn('localhost');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getPort')->willReturn(80);
        
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);
        
        $route = $this->router->match($request);
        
        $this->assertNotNull($route, 'Route should not be null for /old');
        $this->assertSame('REDIRECT', $route->getMethod());
        $this->assertSame('/new', $route->getHandler());
        $this->assertSame(301, $route->getAttributes()['status']);
    }

    public function testUrlGenerationStrictChecks(): void
    {
        $this->router->get('/users/{{user_id}}', 'handler', 'user_detail');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameter "user_id"');
        
        $this->router->url('user_detail', []);
    }

    public function testUrlGenerationWithDefaultsAndOptional(): void
    {
        $this->router->get('/blog/{{slug?}}', 'handler', 'blog_post')->default('slug', 'index');
        
        $this->assertSame('/blog/index', $this->router->url('blog_post', []));
        $this->assertSame('/blog/hello', $this->router->url('blog_post', ['slug' => 'hello']));
    }

    public function testFallbackAtGroupLevel(): void
    {
        $this->router->fallback('global_fallback');
        
        $group = $this->router->group(['prefix' => '/api']);
        $group->fallback('api_fallback');
        
        // Request for /something-else -> global_fallback
        $uri1 = $this->createMock(UriInterface::class);
        $uri1->method('getPath')->willReturn('/something-else');
        $uri1->method('getHost')->willReturn('localhost');
        $uri1->method('getScheme')->willReturn('http');
        $uri1->method('getPort')->willReturn(80);
        
        $request1 = $this->createMock(ServerRequestInterface::class);
        $request1->method('getMethod')->willReturn('GET');
        $request1->method('getUri')->willReturn($uri1);
        
        $route1 = $this->router->match($request1);
        $this->assertNotNull($route1);
        $this->assertSame('global_fallback', $route1->getHandler());

        // Request for /api/nonexistent -> api_fallback
        $uri2 = $this->createMock(UriInterface::class);
        $uri2->method('getPath')->willReturn('/api/nonexistent');
        $uri2->method('getHost')->willReturn('localhost');
        $uri2->method('getScheme')->willReturn('http');
        $uri2->method('getPort')->willReturn(80);
        
        $request2 = $this->createMock(ServerRequestInterface::class);
        $request2->method('getMethod')->willReturn('GET');
        $request2->method('getUri')->willReturn($uri2);
        
        $route2 = $this->router->match($request2);
        $this->assertNotNull($route2);
        $this->assertSame('api_fallback', $route2->getHandler());
    }

    public function testSubdomainConstraints(): void
    {
        $this->router->get('/test', 'handler')->attr('subdomain', 'api');
        
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/test');
        $uri->method('getHost')->willReturn('api.example.com');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getPort')->willReturn(80);
        
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);
        
        $route = $this->router->match($request);
        $this->assertNotNull($route);
        
        $uri2 = $this->createMock(UriInterface::class);
        $uri2->method('getPath')->willReturn('/test');
        $uri2->method('getHost')->willReturn('www.example.com');
        $uri2->method('getScheme')->willReturn('http');
        $uri2->method('getPort')->willReturn(80);
        
        $request2 = $this->createMock(ServerRequestInterface::class);
        $request2->method('getMethod')->willReturn('GET');
        $request2->method('getUri')->willReturn($uri2);
        
        $route2 = $this->router->match($request2);
        $this->assertNull($route2);
    }

    public function testI18nSupport(): void
    {
        $this->router->get('/users', 'UserHandler', 'users')->attr('i18n', [
            'fr' => '/utilisateurs',
            'es' => '/usuarios'
        ]);

        // Default
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/users');
        $uri->method('getHost')->willReturn('localhost');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getPort')->willReturn(80);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);
        $this->assertNotNull($this->router->match($request));

        // French
        $uriFr = $this->createMock(UriInterface::class);
        $uriFr->method('getPath')->willReturn('/utilisateurs');
        $uriFr->method('getHost')->willReturn('localhost');
        $uriFr->method('getScheme')->willReturn('http');
        $uriFr->method('getPort')->willReturn(80);
        $requestFr = $this->createMock(ServerRequestInterface::class);
        $requestFr->method('getMethod')->willReturn('GET');
        $requestFr->method('getUri')->willReturn($uriFr);
        $routeFr = $this->router->match($requestFr);
        $this->assertNotNull($routeFr);
        $this->assertSame('users', $routeFr->getName());
    }
}
