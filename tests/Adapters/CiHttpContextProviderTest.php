<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

use Config\Services;
use Dex\Adapters\CiHttpContextProvider;
use Dex\Tests\Support\DexTestCase;

final class CiHttpContextProviderTest extends DexTestCase
{
    protected function tearDown(): void
    {
        Services::resetSingle('request');

        parent::tearDown();
    }

    public function testBuildWithoutHeaders(): void
    {
        Services::injectMock('request', new HttpRequestFake('/path', 'GET', [], ['X-Test' => 'value']));

        $provider = new CiHttpContextProvider();
        $ctx = $provider->build(false, 10, 50);

        $this->assertSame('GET', $ctx['method']);
        $this->assertSame('/path', $ctx['path']);
        $this->assertArrayNotHasKey('headers', $ctx);
    }

    public function testBuildIncludesLimitedHeaders(): void
    {
        Services::injectMock('request', new HttpRequestFake('/path', 'POST', ['a=1'], [
            'X-First' => '1234567890',
            'X-Second' => 'value2',
        ]));

        $provider = new CiHttpContextProvider();
        $ctx = $provider->build(true, 1, 5);

        $this->assertSame('POST', $ctx['method']);
        $this->assertSame('/path', $ctx['path']);
        $this->assertSame('12345...', $ctx['headers']['X-First']);
        $this->assertCount(1, $ctx['headers']);
    }
}
