<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\Router;
use Atlas\Config\Config;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class ParameterValidationTest extends TestCase
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

    public function testNumericValidation(): void
    {
        $this->router->get('/users/{{id}}', 'handler')->valid('id', 'numeric');
        
        $this->assertNotNull($this->router->match($this->createRequest('GET', '/users/123')));
        $this->assertNull($this->router->match($this->createRequest('GET', '/users/abc')));
    }

    public function testAlphaValidation(): void
    {
        $this->router->get('/tags/{{tag}}', 'handler')->valid('tag', 'alpha');
        
        $this->assertNotNull($this->router->match($this->createRequest('GET', '/tags/php')));
        $this->assertNull($this->router->match($this->createRequest('GET', '/tags/123')));
    }

    public function testRegexValidation(): void
    {
        $this->router->get('/date/{{year}}', 'handler')->valid('year', 'regex:[0-9]{4}');
        
        $this->assertNotNull($this->router->match($this->createRequest('GET', '/date/2024')));
        $this->assertNull($this->router->match($this->createRequest('GET', '/date/24')));
        $this->assertNull($this->router->match($this->createRequest('GET', '/date/abcd')));
    }

    public function testMultipleValidationRules(): void
    {
        // Test that alphanumeric works as expected
        $this->router->get('/product/{{sku}}', 'handler')->valid('sku', 'alphanumeric');
        $this->assertNotNull($this->router->match($this->createRequest('GET', '/product/ABC123')));
        $this->assertNull($this->router->match($this->createRequest('GET', '/product/ABC_123')));
    }

    public function testDefaultValuesMarkParametersAsOptional(): void
    {
        $this->router->get('/shop/{{category}}', 'handler')->default('category', 'all');
        
        $match1 = $this->router->match($this->createRequest('GET', '/shop/electronics'));
        $this->assertNotNull($match1);
        $this->assertSame('electronics', $match1->getAttributes()['category']);
        
        $match2 = $this->router->match($this->createRequest('GET', '/shop'));
        $this->assertNotNull($match2);
        $this->assertSame('all', $match2->getAttributes()['category']);
    }

    public function testOptionalParameterWithDefault(): void
    {
        $this->router->get('/archive/{{year?}}', 'handler')->default('year', 2023);
        
        $match1 = $this->router->match($this->createRequest('GET', '/archive/2024'));
        $this->assertSame('2024', $match1->getAttributes()['year']);
        
        $match2 = $this->router->match($this->createRequest('GET', '/archive'));
        $this->assertSame(2023, $match2->getAttributes()['year']);
    }

    public function testAlphanumericValidation(): void
    {
        $this->router->get('/profile/{{username}}', 'handler')->valid('username', 'alphanumeric');
        
        $this->assertNotNull($this->router->match($this->createRequest('GET', '/profile/user123')));
        $this->assertNull($this->router->match($this->createRequest('GET', '/profile/user_123')));
    }
}
