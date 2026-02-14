<?php

namespace Atlas\Tests\Unit;

use PHPUnit\Framework\TestCase;

class CliToolTest extends TestCase
{
    private string $atlasPath;
    private string $bootstrapDir;
    private string $bootstrapFile;

    protected function setUp(): void
    {
        $this->atlasPath = realpath(__DIR__ . '/../../atlas');
        $this->bootstrapDir = __DIR__ . '/../../bootstrap';
        $this->bootstrapFile = $this->bootstrapDir . '/router.php';

        if (!is_dir($this->bootstrapDir)) {
            mkdir($this->bootstrapDir);
        }

        // Create a bootstrap file for testing
        $content = <<<'PHP'
<?php
use Atlas\Router\Router;
use Atlas\Config\Config;

$config = new Config(['modules_path' => __DIR__ . '/../src/Modules']);
$router = new Router($config);
$router->get('/hello', 'handler', 'hello_route');
$router->get('/users/{{id}}', 'handler', 'user_detail')->valid('id', 'numeric');
return $router;
PHP;
        file_put_contents($this->bootstrapFile, $content);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->bootstrapFile)) {
            unlink($this->bootstrapFile);
        }
        if (is_dir($this->bootstrapDir)) {
            rmdir($this->bootstrapDir);
        }
    }

    public function testRouteList(): void
    {
        exec("php {$this->atlasPath} route:list", $output, $returnCode);

        $this->assertSame(0, $returnCode);
        $this->assertStringContainsString('hello_route', implode("\n", $output));
        $this->assertStringContainsString('user_detail', implode("\n", $output));
    }

    public function testRouteListJson(): void
    {
        exec("php {$this->atlasPath} route:list --json", $output, $returnCode);

        $this->assertSame(0, $returnCode);
        $json = implode("\n", $output);
        $data = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertCount(2, $data);
        $this->assertSame('hello_route', $data[0]['name']);
    }

    public function testRouteTestSuccess(): void
    {
        exec("php {$this->atlasPath} route:test GET /hello", $output, $returnCode);

        $this->assertSame(0, $returnCode);
        $this->assertStringContainsString('Match Found!', implode("\n", $output));
        $this->assertStringContainsString('hello_route', implode("\n", $output));
    }

    public function testRouteTestFailure(): void
    {
        exec("php {$this->atlasPath} route:test GET /nonexistent", $output, $returnCode);

        $this->assertSame(2, $returnCode);
        $this->assertStringContainsString('No Match Found.', implode("\n", $output));
    }

    public function testRouteTestVerbose(): void
    {
        exec("php {$this->atlasPath} route:test GET /users/abc --verbose", $output, $returnCode);

        $this->assertSame(2, $returnCode);
        $this->assertStringContainsString('No Match Found.', implode("\n", $output));
        $this->assertStringContainsString('Diagnostics:', implode("\n", $output));
        $this->assertStringContainsString('user_detail: mismatch', implode("\n", $output));
    }
}
