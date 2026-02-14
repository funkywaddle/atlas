<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\Router;
use Atlas\Config\Config;
use Atlas\Exception\RouteNotFoundException;
use PHPUnit\Framework\TestCase;

class RouterUrlTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $config = new Config(['modules_path' => ['/path/to/modules']]);
        $this->router = new Router($config);
    }

    public function testUrlGeneratesForStaticRoute(): void
    {
        $this->router->get('/users', 'handler', 'user_list');
        
        $url = $this->router->url('user_list');
        
        $this->assertSame('/users', $url);
    }

    public function testUrlGeneratesWithParameters(): void
    {
        $this->router->get('/users/{{user_id}}', 'handler', 'user_detail');
        
        $url = $this->router->url('user_detail', ['user_id' => 42]);
        
        $this->assertSame('/users/42', $url);
    }

    public function testUrlGeneratesWithMultipleParameters(): void
    {
        $this->router->get('/posts/{{post_id}}/comments/{{comment_id}}', 'handler', 'comment_detail');
        
        $url = $this->router->url('comment_detail', [
            'post_id' => 10,
            'comment_id' => 5
        ]);
        
        $this->assertSame('/posts/10/comments/5', $url);
    }

    public function testUrlThrowsExceptionWhenRouteNotFound(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('Route "non_existent" not found');
        
        $this->router->url('non_existent');
    }

    public function testUrlWithMissingParametersThrowsException(): void
    {
        $this->router->get('/users/{{user_id}}', 'handler', 'user_detail');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameter "user_id"');
        
        $this->router->url('user_detail', []);
    }
}
