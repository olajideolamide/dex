<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use Dex\Support\PathHelper;
use PHPUnit\Framework\TestCase;

final class PathHelperTest extends TestCase
{
    public function testNormalizePath(): void
    {
        $this->assertSame('/foo/bar', PathHelper::normalizePath('foo//bar/'));
        $this->assertSame('/foo/bar', PathHelper::normalizePath('/foo/bar?baz=1'));
        $this->assertSame('/', PathHelper::normalizePath('/'));
        $this->assertSame('/a', PathHelper::normalizePath('///a///'));
    }

    public function testSelfRoutesAreIgnoredByDefault(): void
    {
        $config = (object) [
            'routePrefix' => 'dex',
            'ignoreSelfRoutes' => true,
        ];

        $this->assertTrue(PathHelper::isInternalPath('/dex/issues', $config));
    }

    public function testIgnorePrefixesAreApplied(): void
    {
        $config = (object) [
            'ignoreSelfRoutes' => false,
            'ignorePathPrefixes' => ['/health', '/status/'],
        ];

        $this->assertTrue(PathHelper::isInternalPath('/status/check', $config));
        $this->assertFalse(PathHelper::isInternalPath('/public', $config));
    }
}
