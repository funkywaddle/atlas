<?php

namespace Atlas\Tests\Unit;

use Atlas\Router;
use PHPUnit\Framework\TestCase;

class RouterBasicTest extends TestCase
{
    public function testRouterCanBeCreatedWithValidConfig(): void
    {
        $config = new \Atlas\Config([
            'modules_path' => ['/path/to/modules'],
            'routes_file' => 'routes.php'
        ]);

        $router = new Router($config);

        $this->assertInstanceOf(Router::class, $router);
    }

    public function testRouterCanCreateSimpleRoute(): void
    {
        $config = new \Atlas\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new Router($config);

        $router->get('/hello', function() {
            return 'Hello World';
        });

        $this->assertCount(1, $router->getRoutes());
    }

    public function testRouterReturnsSameInstanceForChaining(): void
    {
        $config = new \Atlas\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new Router($config);

        $result = $router->get('/get', 'handler')->post('/post', 'handler');

        $this->assertTrue($result instanceof Router);
    }

    public function testRouteHasCorrectProperties(): void
    {
        $config = new \Atlas\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new Router($config);

        $router->get('/test', 'test_handler', 'test_route');

        $routes = $router->getRoutes();
        $route = $routes[0] ?? null;

        $this->assertInstanceOf(\Atlas\RouteDefinition::class, $route);
        $this->assertSame('GET', $route->getMethod());
        $this->assertSame('/test', $route->getPath());
        $this->assertSame('test_handler', $route->getHandler());
        $this->assertSame('test_route', $route->getName());
        $this->assertEmpty($route->getMiddleware());
        $this->assertEmpty($route->getValidation());
        $this->assertEmpty($route->getDefaults());
    }

    public function testRouteNormalizesPath(): void
    {
        $config = new \Atlas\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new Router($config);

        $router->get('/api/test', 'handler');

        $routes = $router->getRoutes();
        $route = $routes[0] ?? null;

        $this->assertInstanceOf(\Atlas\RouteDefinition::class, $route);
        $this->assertSame('/api/test', $route->getPath());
    }
}