<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\Router;
use Atlas\Config\Config;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class DynamicMatchingTest extends TestCase
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

    public function testMatchesDynamicParameters(): void
    {
        $this->router->get('/users/{{user_id}}', 'handler');
        
        $request = $this->createRequest('GET', '/users/42');
        $match = $this->router->match($request);
        
        $this->assertNotNull($match);
        $this->assertSame('42', $match->getAttributes()['user_id']);
    }

    public function testMatchesMultipleParameters(): void
    {
        $this->router->get('/posts/{{post_id}}/comments/{{comment_id}}', 'handler');
        
        $request = $this->createRequest('GET', '/posts/10/comments/5');
        $match = $this->router->match($request);
        
        $this->assertNotNull($match);
        $this->assertSame('10', $match->getAttributes()['post_id']);
        $this->assertSame('5', $match->getAttributes()['comment_id']);
    }

    public function testMatchesOptionalParameters(): void
    {
        $this->router->get('/blog/{{slug?}}', 'handler');
        
        // With parameter
        $request1 = $this->createRequest('GET', '/blog/my-post');
        $match1 = $this->router->match($request1);
        $this->assertNotNull($match1);
        $this->assertSame('my-post', $match1->getAttributes()['slug']);
        
        // Without parameter
        $request2 = $this->createRequest('GET', '/blog');
        $match2 = $this->router->match($request2);
        $this->assertNotNull($match2);
        $this->assertArrayNotHasKey('slug', $match2->getAttributes());
    }

    public function testFluentConfiguration(): void
    {
        $route = $this->router->get('/test', 'handler')
            ->name('test_route')
            ->middleware('auth')
            ->attr('key', 'value');
            
        $this->assertSame('test_route', $route->getName());
        $this->assertContains('auth', $route->getMiddleware());
        $this->assertSame('value', $route->getAttributes()['key']);
    }

    public function testNestedGroupsInheritPrefixAndMiddleware(): void
    {
        $group = $this->router->group(['prefix' => '/api', 'middleware' => ['api_middleware']]);
        
        $nested = $group->group(['prefix' => '/v1', 'middleware' => ['v1_middleware']]);
        
        $route = $nested->get('/users', 'handler');
        
        $this->assertSame('/api/v1/users', $route->getPath());
        $this->assertContains('api_middleware', $route->getMiddleware());
        $this->assertContains('v1_middleware', $route->getMiddleware());
    }

    public function testDefaultValuesAndValidation(): void
    {
        $this->router->get('/blog/{{page}}', 'handler')
            ->default('page', 1)
            ->valid('page', ['int']);
            
        // With value
        $request1 = $this->createRequest('GET', '/blog/5');
        $match1 = $this->router->match($request1);
        $this->assertNotNull($match1);
        $this->assertSame('5', $match1->getAttributes()['page']);
        
        // With default
        $request2 = $this->createRequest('GET', '/blog');
        $match2 = $this->router->match($request2);
        $this->assertNotNull($match2);
        $this->assertSame(1, $match2->getAttributes()['page']);
        
        // Invalid value (non-int)
        $request3 = $this->createRequest('GET', '/blog/abc');
        $match3 = $this->router->match($request3);
        $this->assertNull($match3);
    }
}
