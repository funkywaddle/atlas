<?php

namespace Atlas\Tests\Unit;

use Atlas\Config\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testGetReturnsValueOrPlaceholder(): void
    {
        $config = new Config(['key' => 'value']);
        
        $this->assertSame('value', $config->get('key'));
        $this->assertSame('default', $config->get('non_existent', 'default'));
        $this->assertNull($config->get('non_existent'));
    }

    public function testHasChecksExistence(): void
    {
        $config = new Config(['key' => 'value']);
        
        $this->assertTrue($config->has('key'));
        $this->assertFalse($config->has('non_existent'));
    }

    public function testGetModulesPathNormalizesToArray(): void
    {
        $config1 = new Config(['modules_path' => '/single/path']);
        $this->assertSame(['/single/path'], $config1->getModulesPath());

        $config2 = new Config(['modules_path' => ['/path/1', '/path/2']]);
        $this->assertSame(['/path/1', '/path/2'], $config2->getModulesPath());

        $config3 = new Config([]);
        $this->assertNull($config3->getModulesPath());
    }

    public function testGetModulesPathListAlwaysReturnsArray(): void
    {
        $config1 = new Config(['modules_path' => '/single/path']);
        $this->assertSame(['/single/path'], $config1->getModulesPathList());

        $config2 = new Config(['modules_path' => ['/path/1', '/path/2']]);
        $this->assertSame(['/path/1', '/path/2'], $config2->getModulesPathList());

        $config3 = new Config([]);
        $this->assertSame([], $config3->getModulesPathList());
    }

    public function testGetRoutesFileWithDefault(): void
    {
        $config1 = new Config(['routes_file' => 'custom.php']);
        $this->assertSame('custom.php', $config1->getRoutesFile());

        $config2 = new Config([]);
        $this->assertSame('routes.php', $config2->getRoutesFile());
    }

    public function testGetModulesGlob(): void
    {
        $config1 = new Config(['modules_glob' => 'src/*/routes.php']);
        $this->assertSame('src/*/routes.php', $config1->getModulesGlob());

        $config2 = new Config([]);
        $this->assertNull($config2->getModulesGlob());
    }

    public function testToArray(): void
    {
        $options = ['a' => 1, 'b' => 2];
        $config = new Config($options);
        
        $this->assertSame($options, $config->toArray());
    }

    public function testArrayAccess(): void
    {
        $config = new Config(['key' => 'value']);
        
        $this->assertTrue(isset($config['key']));
        $this->assertSame('value', $config['key']);
        
        $config['new'] = 'val';
        $this->assertSame('val', $config['new']);
        
        unset($config['key']);
        $this->assertFalse(isset($config['key']));
    }

    public function testIteratorAggregate(): void
    {
        $options = ['a' => 1, 'b' => 2];
        $config = new Config($options);
        
        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = $value;
        }
        
        $this->assertSame($options, $result);
    }
}
