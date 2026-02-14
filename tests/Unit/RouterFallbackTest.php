<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\Router;
use Atlas\Config\Config;
use PHPUnit\Framework\TestCase;

class RouterFallbackTest extends TestCase
{
    public function testFallbackHandlerCanBeSet(): void
    {
        $config = new Config(['modules_path' => ['/path/to/modules']]);
        $router = new Router($config);
        
        $handler = function() { return '404'; };
        $router->fallback($handler);
        
        // Use reflection to check if fallbackHandler is set correctly since there is no getter
        $reflection = new \ReflectionClass($router);
        $property = $reflection->getProperty('fallbackHandler');
        $property->setAccessible(true);
        
        $this->assertSame($handler, $property->getValue($router));
    }

    public function testFallbackReturnsRouterInstanceForChaining(): void
    {
        $config = new Config(['modules_path' => ['/path/to/modules']]);
        $router = new Router($config);
        
        $result = $router->fallback('Handler');
        
        $this->assertSame($router, $result);
    }
}
