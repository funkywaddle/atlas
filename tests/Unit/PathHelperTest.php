<?php

namespace Atlas\Tests\Unit;

use Atlas\Router\PathHelper;
use PHPUnit\Framework\TestCase;

class PathHelperTest extends TestCase
{
    private $helper;

    protected function setUp(): void
    {
        $this->helper = new class {
            use PathHelper;
            
            public function testNormalize(string $path): string
            {
                return $this->normalizePath($path);
            }
            
            public function testJoin(string $prefix, string $path): string
            {
                return $this->joinPaths($prefix, $path);
            }
        };
    }

    public function testNormalizePathEnsuresLeadingSlash(): void
    {
        $this->assertSame('/test', $this->helper->testNormalize('test'));
        $this->assertSame('/test', $this->helper->testNormalize('/test'));
    }

    public function testNormalizePathRemovesTrailingSlash(): void
    {
        $this->assertSame('/test', $this->helper->testNormalize('test/'));
        $this->assertSame('/test', $this->helper->testNormalize('/test/'));
    }

    public function testNormalizePathHandlesEmptyOrSlash(): void
    {
        $this->assertSame('/', $this->helper->testNormalize(''));
        $this->assertSame('/', $this->helper->testNormalize('/'));
        $this->assertSame('/', $this->helper->testNormalize('///'));
    }

    public function testJoinPathsCombinesCorrectly(): void
    {
        $this->assertSame('/api/users', $this->helper->testJoin('/api', 'users'));
        $this->assertSame('/api/users', $this->helper->testJoin('/api/', '/users'));
        $this->assertSame('/api/users', $this->helper->testJoin('api', 'users'));
    }

    public function testJoinPathsHandlesRootPrefix(): void
    {
        $this->assertSame('/users', $this->helper->testJoin('/', 'users'));
        $this->assertSame('/users', $this->helper->testJoin('', 'users'));
    }
}
