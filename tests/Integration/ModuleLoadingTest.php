<?php

namespace Atlas\Tests\Integration;

use Atlas\Router\Router;
use Atlas\Config\Config;
use Atlas\Exception\MissingConfigurationException;
use PHPUnit\Framework\TestCase;

class ModuleLoadingTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/atlas_test_' . uniqid();
        mkdir($this->tempDir);
        mkdir($this->tempDir . '/User');
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    private function removeDirectory(string $path): void
    {
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$path/$file")) ? $this->removeDirectory("$path/$file") : unlink("$path/$file");
        }
        rmdir($path);
    }

    public function testModuleLoadsRoutesFromFilesystem(): void
    {
        $routesContent = '<?php return [["method" => "GET", "path" => "/profile", "handler" => "UserHandler", "name" => "user_profile"]];';
        file_put_contents($this->tempDir . '/User/routes.php', $routesContent);

        $config = new Config([
            'modules_path' => [$this->tempDir],
            'routes_file' => 'routes.php'
        ]);

        $router = new Router($config);
        $router->module('User');

        $routes = iterator_to_array($router->getRoutes());
        $this->assertCount(1, $routes);
        $this->assertSame('/profile', $routes[0]->getPath());
        $this->assertSame('user_profile', $routes[0]->getName());
    }

    public function testModuleThrowsExceptionWhenModulesPathIsMissing(): void
    {
        $config = new Config([]);
        $router = new Router($config);

        $this->expectException(MissingConfigurationException::class);
        $router->module('User');
    }

    public function testModuleSkipsWhenRoutesFileDoesNotExist(): void
    {
        $config = new Config([
            'modules_path' => [$this->tempDir]
        ]);

        $router = new Router($config);
        $router->module('NonExistent');

        $this->assertCount(0, $router->getRoutes());
    }

    public function testModuleWithMultipleSearchPaths(): void
    {
        $secondPath = sys_get_temp_dir() . '/atlas_test_2_' . uniqid();
        mkdir($secondPath);
        mkdir($secondPath . '/Shared');
        
        $routesContent = '<?php return [["method" => "GET", "path" => "/shared", "handler" => "SharedHandler"]];';
        file_put_contents($secondPath . '/Shared/routes.php', $routesContent);

        $config = new Config([
            'modules_path' => [$this->tempDir, $secondPath]
        ]);

        $router = new Router($config);
        $router->module('Shared');

        $routes = iterator_to_array($router->getRoutes());
        $this->assertCount(1, $routes);
        $this->assertSame('/shared', $routes[0]->getPath());

        $this->removeDirectory($secondPath);
    }

    public function testModuleLoadsMultipleRoutes(): void
    {
        $routesContent = '<?php return [
            ["method" => "GET", "path" => "/u1", "handler" => "h1"],
            ["method" => "POST", "path" => "/u2", "handler" => "h2"]
        ];';
        file_put_contents($this->tempDir . '/User/routes.php', $routesContent);

        $config = new Config(['modules_path' => $this->tempDir]);
        $router = new Router($config);
        $router->module('User');

        $this->assertCount(2, $router->getRoutes());
    }
}
