<?php

declare(strict_types=1);

namespace Dex\Tests\Core;

use Dex\DTO\RequestMeta;
use Dex\Services\Core\RequestContextService;
use PHPUnit\Framework\TestCase;

final class RequestContextServiceTest extends TestCase
{
    public function testDoesNotStoreNormalOrSlowRequestWithoutError(): void
    {
        $service = $this->makeService();

        $service->start(new RequestMeta('GET', '/slow', '127.0.0.1', 'UA', null));

        [$shouldStore, $slowHit, $sampleHit] = $service->shouldStoreRequest(200, 250);

        $this->assertFalse($shouldStore);
        $this->assertFalse($slowHit);
        $this->assertFalse($sampleHit);
    }

    public function testStoresRequestWhenErrorWasMarked(): void
    {
        $service = $this->makeService();

        $service->start(new RequestMeta('GET', '/boom', '127.0.0.1', 'UA', null));
        $service->markError();

        [$shouldStore, $slowHit, $sampleHit] = $service->shouldStoreRequest(200, 150);

        $this->assertTrue($shouldStore);
        $this->assertFalse($slowHit);
        $this->assertFalse($sampleHit);
    }

    public function testStoresRequestForServerErrorStatus(): void
    {
        $service = $this->makeService();

        $service->start(new RequestMeta('GET', '/fail', '127.0.0.1', 'UA', null));

        [$shouldStore, $slowHit, $sampleHit] = $service->shouldStoreRequest(500, 50);

        $this->assertTrue($shouldStore);
        $this->assertFalse($slowHit);
        $this->assertFalse($sampleHit);
    }

    public function testAttachRawRequestStoresSnapshotRequestObject(): void
    {
        $service = $this->makeService();
        $request = new \stdClass();

        $service->start(new RequestMeta('GET', '/snap', '127.0.0.1', 'UA', null));
        $service->attachRawRequest($request);

        $ctx = $service->getContext();

        $this->assertSame($request, $ctx['_request'] ?? null);
    }

    private function makeService(array $overrides = []): RequestContextService
    {
        $config = (object) array_merge([], $overrides);

        return new RequestContextService($config);
    }
}
