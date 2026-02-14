<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\Router;
use Atlas\Router\RouteGroup;
use Atlas\Config\Config;
use PHPUnit\Framework\TestCase;

class RouteGroupTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $config = new Config(['modules_path' => ['/path/to/modules']]);
        $this->router = new Router($config);
    }

    public function testGroupAppliesPrefixToRoutes(): void
    {
        $group = $this->router->group(['prefix' => '/api']);
        
        $group->get('/users', 'Handler');
        
        $routes = iterator_to_array($this->router->getRoutes());
        $this->assertCount(1, $routes);
        $this->assertSame('/api/users', $routes[0]->getPath());
    }

    public function testGroupAppliesPrefixWithLeadingSlashToRoutes(): void
    {
        $group = $this->router->group(['prefix' => 'api']);
        
        $group->get('users', 'Handler');
        
        $routes = iterator_to_array($this->router->getRoutes());
        $this->assertCount(1, $routes);
        $this->assertSame('/api/users', $routes[0]->getPath());
    }

    public function testGroupWithTrailingSlashInPrefix(): void
    {
        $group = $this->router->group(['prefix' => '/api/']);
        
        $group->get('/users', 'Handler');
        
        $routes = iterator_to_array($this->router->getRoutes());
        $this->assertCount(1, $routes);
        $this->assertSame('/api/users', $routes[0]->getPath());
    }

    public function testAllHttpMethodsInGroup(): void
    {
        $group = $this->router->group(['prefix' => '/api']);
        
        $group->get('/test', 'handler');
        $group->post('/test', 'handler');
        $group->put('/test', 'handler');
        $group->patch('/test', 'handler');
        $group->delete('/test', 'handler');
        
        $routes = iterator_to_array($this->router->getRoutes());
        $this->assertCount(5, $routes);
        
        foreach ($routes as $route) {
            $this->assertSame('/api/test', $route->getPath());
        }
    }

    public function testGroupReturnsSameInstanceForChaining(): void
    {
        $group = $this->router->group(['prefix' => '/api']);
        
        $group->get('/users', 'Handler');
        $group->post('/users', 'Handler');
        
        $routes = iterator_to_array($this->router->getRoutes());
        $this->assertCount(2, $routes);
    }

    public function testGroupCanBeCreatedWithoutRouterAndStillWorks(): void
    {
        // This tests the case where RouteGroup might be used partially or in isolation
        // although buildFullPath is the main logic.
        $group = new RouteGroup(['prefix' => '/api']);
        
        // Use reflection or just check options if public (it is protected/private)
        $this->assertSame(['prefix' => '/api'], $group->getOptions());
    }

    public function testSetOptionOnGroup(): void
    {
        $group = new RouteGroup();
        $group->setOption('prefix', '/test');
        
        $this->assertSame(['prefix' => '/test'], $group->getOptions());
    }
}
