<?php

declare(strict_types=1);

namespace Dex\Tests\Core;

use Dex\Services\Core\OccurrenceWriterService;
use Dex\Tests\Support\Doubles\MemoryStorage;
use PHPUnit\Framework\TestCase;

final class OccurrenceWriterServiceTest extends TestCase
{
    public function testDropsDuplicatedRequestScopedPayloadWhenRequestIdExists(): void
    {
        $storage = new MemoryStorage();
        $service = new OccurrenceWriterService($storage, (object) ['scrubFields' => []]);

        $service->write(
            ['fingerprint' => 'fp-1', 'level' => 'error', 'title' => 'Failure'],
            'Something broke',
            [
                'request' => ['method' => 'POST'],
                'http' => ['url' => 'https://app.test/fail'],
                'breadcrumbs' => [['message' => 'breadcrumb']],
                'spans' => [['name' => 'db.query']],
                'tags' => ['environment' => 'production'],
                'exception' => ['class' => 'RuntimeException'],
            ],
            'req-1'
        );

        $payload = json_decode((string) $storage->occurrences[0]['context'], true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayNotHasKey('request', $payload);
        $this->assertArrayNotHasKey('http', $payload);
        $this->assertArrayNotHasKey('breadcrumbs', $payload);
        $this->assertArrayNotHasKey('spans', $payload);
        $this->assertSame('production', $payload['tags']['environment']);
        $this->assertSame('RuntimeException', $payload['exception']['class']);
    }

    public function testKeepsLegacyContextForRequestlessOccurrences(): void
    {
        $storage = new MemoryStorage();
        $service = new OccurrenceWriterService($storage, (object) ['scrubFields' => []]);

        $service->write(
            ['fingerprint' => 'fp-2', 'level' => 'error', 'title' => 'Failure'],
            'Something broke',
            [
                'request' => ['method' => 'POST'],
                'http' => ['url' => 'https://app.test/fail'],
                'breadcrumbs' => [['message' => 'breadcrumb']],
                'spans' => [['name' => 'db.query']],
            ],
            null
        );

        $payload = json_decode((string) $storage->occurrences[0]['context'], true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('request', $payload);
        $this->assertArrayHasKey('http', $payload);
        $this->assertArrayHasKey('breadcrumbs', $payload);
        $this->assertArrayHasKey('spans', $payload);
    }
}
