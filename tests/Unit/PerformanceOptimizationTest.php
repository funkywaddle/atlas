<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\Router;
use Atlas\Router\RouteCollection;
use Atlas\Router\RouteDefinition;
use Atlas\Config\Config;
use PHPUnit\Framework\TestCase;

class PerformanceOptimizationTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $config = new Config(['modules_path' => ['/path/to/modules']]);
        $this->router = new Router($config);
    }

    public function testRouteCollectionIsSerializable(): void
    {
        $this->router->get('/users', 'UserHandler', 'user_list');
        $this->router->get('/users/{{id}}', 'UserDetailHandler', 'user_detail')->valid('id', 'numeric');
        
        $routes = $this->router->getRoutes();
        $serialized = serialize($routes);
        
        /** @var RouteCollection $unserialized */
        $unserialized = unserialize($serialized);
        
        $this->assertInstanceOf(RouteCollection::class, $unserialized);
        $this->assertCount(2, iterator_to_array($unserialized));
        
        $route = $unserialized->getByName('user_detail');
        $this->assertNotNull($route);
        $this->assertSame('/users/{{id}}', $route->getPath());
        $this->assertSame(['id' => ['numeric']], $route->getValidation());
    }

    public function testRouterCanLoadCachedRoutes(): void
    {
        $this->router->get('/old', 'OldHandler');
        
        $newCollection = new RouteCollection();
        $newCollection->add(new RouteDefinition('GET', '/new', '/new', 'NewHandler', 'new_route'));
        
        $this->router->setRoutes($newCollection);
        
        $routes = iterator_to_array($this->router->getRoutes());
        $this->assertCount(1, $routes);
        $this->assertSame('/new', $routes[0]->getPath());
    }

    public function testMatcherCacheWorks(): void
    {
        // Internal test to ensure compilePattern doesn't re-run unnecessarily
        // Hard to test private cache directly without reflection, but we can verify it doesn't break matching
        $this->router->get('/test/{{id}}', 'handler');
        
        $uri = $this->createMock(\Psr\Http\Message\UriInterface::class);
        $uri->method('getPath')->willReturn('/test/123');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getHost')->willReturn('localhost');
        
        $request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);
        
        $match1 = $this->router->match($request);
        $this->assertNotNull($match1);
        
        $match2 = $this->router->match($request);
        $this->assertNotNull($match2);
        $this->assertSame($match1->getHandler(), $match2->getHandler());
    }
}
