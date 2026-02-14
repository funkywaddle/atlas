<?php

namespace Atlas\Tests\Integration;

use Atlas\Router\Router;
use Atlas\Config\Config;
use PHPUnit\Framework\TestCase;

class ModuleDiscoveryTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/atlas_module_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) return;
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$path/$file")) ? $this->removeDirectory("$path/$file") : unlink("$path/$file");
        }
        rmdir($path);
    }

    private function createModule(string $name, string $content): void
    {
        mkdir($this->tempDir . '/' . $name, 0777, true);
        file_put_contents($this->tempDir . '/' . $name . '/routes.php', $content);
    }

    public function testModuleDiscoveryWithPrefix(): void
    {
        $routesContent = '<?php return [["method" => "GET", "path" => "/index", "handler" => "UserHandler"]];';
        $this->createModule('User', $routesContent);

        $config = new Config(['modules_path' => $this->tempDir]);
        $router = new Router($config);
        $router->module('User', '/user');

        $routes = iterator_to_array($router->getRoutes());
        $this->assertCount(1, $routes);
        $this->assertSame('/user/index', $routes[0]->getPath());
        $this->assertSame('User', $routes[0]->getModule());
    }

    public function testModuleInheritanceOfMiddlewareAndValidation(): void
    {
        $routesContent = '<?php return [
            [
                "method" => "POST", 
                "path" => "/save", 
                "handler" => "SaveHandler",
                "middleware" => ["module_mid"],
                "validation" => ["id" => "numeric"]
            ]
        ];';
        $this->createModule('Admin', $routesContent);

        $config = new Config(['modules_path' => $this->tempDir]);
        $router = new Router($config);
        
        // Group at router level
        $router->group(['middleware' => ['global_mid']])->module('Admin', '/admin');

        $routes = iterator_to_array($router->getRoutes());
        $this->assertCount(1, $routes);
        $route = $routes[0];
        
        $this->assertSame('/admin/save', $route->getPath());
        $this->assertContains('global_mid', $route->getMiddleware());
        $this->assertContains('module_mid', $route->getMiddleware());
        $this->assertArrayHasKey('id', $route->getValidation());
        $this->assertSame('numeric', $route->getValidation()['id'][0]);
        $this->assertSame('Admin', $route->getModule());
    }

    public function testOverlappingModuleRoutes(): void
    {
        // Conflict resolution: first registered wins or both stay? 
        // Typically, router matches sequentially, so first registered wins.
        
        $userRoutes = '<?php return [["method" => "GET", "path" => "/profile", "handler" => "UserHandler"]];';
        $this->createModule('User', $userRoutes);
        
        $adminRoutes = '<?php return [["method" => "GET", "path" => "/profile", "handler" => "AdminHandler"]];';
        $this->createModule('Admin', $adminRoutes);

        $config = new Config(['modules_path' => $this->tempDir]);
        $router = new Router($config);
        
        $router->module('User', '/common');
        $router->module('Admin', '/common');

        $routes = iterator_to_array($router->getRoutes());
        $this->assertCount(2, $routes);
        $this->assertSame('/common/profile', $routes[0]->getPath());
        $this->assertSame('UserHandler', $routes[0]->getHandler());
        $this->assertSame('/common/profile', $routes[1]->getPath());
        $this->assertSame('AdminHandler', $routes[1]->getHandler());
    }
}
