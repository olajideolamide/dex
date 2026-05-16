<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

use Dex\Adapters\CiRequestMetaFactory;
use PHPUnit\Framework\TestCase;

final class CiRequestMetaFactoryTest extends TestCase
{
    public function testBuildsRequestMetaFromRequest(): void
    {
        $request = new StubRequest(
            new StubUri('/hello'),
            'POST',
            '127.0.0.1',
            'TestAgent',
            ['X-Request-Id' => ' abc123 ']
        );

        $config = (object) ['requestIdHeader' => 'X-Request-Id'];

        $meta = CiRequestMetaFactory::fromRequest($request, $config);

        $this->assertSame('POST', $meta->method);
        $this->assertSame('/hello', $meta->rawPath);
        $this->assertSame('127.0.0.1', $meta->ip);
        $this->assertSame('TestAgent', $meta->userAgent);
        $this->assertSame('abc123', $meta->incomingRequestId);
    }

    public function testEmptyHeaderYieldsNullIncomingId(): void
    {
        $request = new StubRequest(new StubUri('/'), 'GET', null, null, []);
        $config = (object) ['requestIdHeader' => 'X-Request-Id'];

        $meta = CiRequestMetaFactory::fromRequest($request, $config);

        $this->assertNull($meta->incomingRequestId);
    }
}
