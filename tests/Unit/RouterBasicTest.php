<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\Router;
use PHPUnit\Framework\TestCase;

class RouterBasicTest extends TestCase
{
    public function testRouterCanBeCreatedWithValidConfig(): void
    {
        $config = new \Atlas\Config\Config([
            'modules_path' => ['/path/to/modules'],
            'routes_file' => 'routes.php'
        ]);

        $router = new \Atlas\Router\Router($config);

        $this->assertInstanceOf(\Atlas\Router\Router::class, $router);
    }

    public function testRouterCanCreateSimpleRoute(): void
    {
        $config = new \Atlas\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/hello', function() {
            return 'Hello World';
        });

        $this->assertCount(1, $router->getRoutes());
    }

    public function testRouterReturnsSameInstanceForChaining(): void
    {
        $config = new \Atlas\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/get', 'handler');
        $router->post('/post', 'handler');

        $this->assertCount(2, $router->getRoutes());
    }

    public function testRouteHasCorrectProperties(): void
    {
        $config = new \Atlas\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/test', 'test_handler', 'test_route');

        $routes = iterator_to_array($router->getRoutes());
        $route = $routes[0] ?? null;

        $this->assertInstanceOf(\Atlas\Router\RouteDefinition::class, $route);
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
        $config = new \Atlas\Config\Config([
            'modules_path' => ['/path/to/modules']
        ]);

        $router = new \Atlas\Router\Router($config);

        $router->get('/api/test', 'handler');

        $routes = iterator_to_array($router->getRoutes());
        $route = $routes[0] ?? null;

        $this->assertInstanceOf(\Atlas\Router\RouteDefinition::class, $route);
        $this->assertSame('/api/test', $route->getPath());
    }
}