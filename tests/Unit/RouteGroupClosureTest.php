<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\Router;
use Atlas\Config\Config;
use PHPUnit\Framework\TestCase;

class RouteGroupClosureTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $config = new Config(['modules_path' => ['/path/to/modules']]);
        $this->router = new Router($config);
    }

    public function testGroupSupportsClosureRegistration(): void
    {
        $this->router->group(['prefix' => '/api'])->group(function($group) {
            $group->get('/users', 'UserHandler');
            $group->post('/users', 'UserCreateHandler');
        });

        $routes = iterator_to_array($this->router->getRoutes());
        $this->assertCount(2, $routes);
        $this->assertSame('/api/users', $routes[0]->getPath());
        $this->assertSame('GET', $routes[0]->getMethod());
        $this->assertSame('/api/users', $routes[1]->getPath());
        $this->assertSame('POST', $routes[1]->getMethod());
    }

    public function testNestedGroupClosureInheritance(): void
    {
        $this->router->group(['prefix' => '/api', 'middleware' => ['auth']])->group(function($group) {
            $group->group(['prefix' => '/v1'])->group(function($v1) {
                $v1->get('/profile', 'ProfileHandler');
            });
        });

        $routes = iterator_to_array($this->router->getRoutes());
        $this->assertCount(1, $routes);
        $this->assertSame('/api/v1/profile', $routes[0]->getPath());
        $this->assertContains('auth', $routes[0]->getMiddleware());
    }
}
