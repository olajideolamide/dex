<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use Dex\DTO\ResponseMeta;
use Dex\Support\RequestSnapshot;
use PHPUnit\Framework\TestCase;

final class RequestSnapshotTest extends TestCase
{
    public function testBuildCapturesRequestAndResponseMetadata(): void
    {
        $req = new StubRequest(
            new FakeUri('https://example.test/path?foo=bar'),
            ['foo' => '1'],
            ['bar' => '2'],
            ['file' => ['name' => 'x.txt']],
            ['X-Request-Id' => 'abc', 'Authorization' => 'secret', 'CF-IPCountry' => 'ng'],
            true
        );

        $config = (object) [
            'snapshotIncludeInputKeys' => true,
            'snapshotMaxKeys' => 5,
            'snapshotIncludeHeaders' => true,
            'snapshotHeaderAllowlist' => ['x-request-id'],
        ];

        $ctx = [
            'request_id' => 'req-123',
            'controller' => 'Home',
            'action' => 'index',
            'route' => 'home',
            'route_params' => ['id' => 5],
            'method' => 'GET',
            'path' => '/path',
            'ip' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            '_duration_ms' => 12.3,
            '_mem_peak' => 2048,
            'db_count' => 2,
            'db_time_ms' => 45.6,
            'breadcrumbs' => ['a', 'b'],
            'spans' => ['s1'],
            '_sample_hit' => true,
            '_slow_hit' => false,
            'had_error' => true,
            '_request' => $req,
        ];

        $response = new ResponseMeta(201, ['Content-Type' => 'application/json']);

        $snapshot = RequestSnapshot::build($ctx, $config, $response);

        $this->assertSame('req-123', $snapshot['request_id']);
        $this->assertSame(PHP_VERSION, $snapshot['ci']['php']);
        $this->assertSame(PHP_SAPI, $snapshot['ci']['sapi']);
        $this->assertSame('Home', $snapshot['routing']['controller']);
        $this->assertSame('GET', $snapshot['request']['method']);
        $this->assertSame('/path', $snapshot['request']['path']);
        $this->assertSame('https://example.test/path?foo=bar', $snapshot['request']['url']);
        $this->assertSame('foo=bar', $snapshot['request']['query']);
        $this->assertSame('example.test', $snapshot['request']['host']);
        $this->assertSame('https', $snapshot['request']['scheme']);
        $this->assertTrue($snapshot['request']['is_ajax']);
        $this->assertSame('NG', $snapshot['user']['country']);
        $this->assertSame('Unknown', $snapshot['user']['browser']);
        $this->assertSame(PHP_VERSION, $snapshot['server']['php']['version']);
        $this->assertSame(PHP_OS_FAMILY, $snapshot['server']['os']['family']);

        $this->assertSame(201, $snapshot['response']['status_code']);
        $this->assertSame('application/json', $snapshot['response']['content_type']);
        $this->assertSame(46, $snapshot['metrics']['db_time_ms']);

        $this->assertSame(['foo'], $snapshot['input']['get']);
        $this->assertSame(['bar'], $snapshot['input']['post']);
        $this->assertSame(['file'], $snapshot['input']['files']);

        $this->assertSame(['X-Request-Id' => 'abc'], $snapshot['headers']);
    }
}
