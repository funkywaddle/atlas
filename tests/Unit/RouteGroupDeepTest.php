<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\Router;
use Atlas\Config\Config;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class RouteGroupDeepTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $config = new Config(['modules_path' => ['/path/to/modules']]);
        $this->router = new Router($config);
    }

    private function createRequest(string $method, string $path): ServerRequestInterface
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn($path);
        
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn($method);
        $request->method('getUri')->willReturn($uri);
        
        return $request;
    }

    public function testIndefiniteNestingInheritance(): void
    {
        $this->router->group(['prefix' => '/api', 'middleware' => ['api']])
            ->group(['prefix' => '/v1', 'middleware' => ['v1']])
            ->group(['prefix' => '/users', 'middleware' => ['users']])
            ->get('/list', 'handler', 'user_list');

        $routes = iterator_to_array($this->router->getRoutes());
        $route = $routes[0];

        $this->assertSame('/api/v1/users/list', $route->getPath());
        $this->assertContains('api', $route->getMiddleware());
        $this->assertContains('v1', $route->getMiddleware());
        $this->assertContains('users', $route->getMiddleware());
    }

    public function testGroupLevelValidation(): void
    {
        $group = $this->router->group(['prefix' => '/users/{{user_id}}'])
            ->valid('user_id', 'numeric');

        $group->get('/profile', 'profile_handler');
        $group->get('/posts', 'posts_handler');

        // Valid match
        $request1 = $this->createRequest('GET', '/users/123/profile');
        $match1 = $this->router->match($request1);
        $this->assertNotNull($match1);
        $this->assertSame('123', $match1->getAttributes()['user_id']);

        // Invalid match (non-numeric)
        $request2 = $this->createRequest('GET', '/users/abc/profile');
        $match2 = $this->router->match($request2);
        $this->assertNull($match2);
    }

    public function testGroupLevelDefaults(): void
    {
        $group = $this->router->group(['prefix' => '/blog/{{lang?}}'])
            ->default('lang', 'en');

        $group->get('/recent', 'recent_handler');

        // With value
        $request1 = $this->createRequest('GET', '/blog/fr/recent');
        $match1 = $this->router->match($request1);
        $this->assertSame('fr', $match1->getAttributes()['lang']);

        // With default
        $request2 = $this->createRequest('GET', '/blog/recent');
        $match2 = $this->router->match($request2);
        $this->assertSame('en', $match2->getAttributes()['lang']);
    }

    public function testFluentGroupConfiguration(): void
    {
        $group = $this->router->group(['prefix' => '/admin']);
        $group->valid('id', 'numeric')->default('id', 0);

        $route = $group->get('/dashboard/{{id}}', 'handler');

        $this->assertSame('/admin/dashboard/{{id}}', $route->getPath());
        $this->assertArrayHasKey('id', $route->getValidation());
        $this->assertSame(0, $route->getDefaults()['id']);
    }
}
