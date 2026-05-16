<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use Dex\Support\Scrubber;
use PHPUnit\Framework\TestCase;

final class ScrubberTest extends TestCase
{
    public function testScrubRedactsKeysCaseInsensitive(): void
    {
        $data = [
            'user' => [
                'Password' => 'secret',
                'name' => 'Ada',
            ],
            'token' => 'abc',
        ];

        $scrubbed = Scrubber::scrub($data, ['password', 'token']);

        $this->assertSame('[REDACTED]', $scrubbed['user']['Password']);
        $this->assertSame('[REDACTED]', $scrubbed['token']);
        $this->assertSame('Ada', $scrubbed['user']['name']);
    }

    public function testScrubClampsLongStrings(): void
    {
        $input = str_repeat('a', 2100);
        $scrubbed = Scrubber::scrub(['v' => $input], []);

        $this->assertNotSame($input, $scrubbed['v']);
        $this->assertSame(str_repeat('a', 2000), mb_substr($scrubbed['v'], 0, 2000));
        $this->assertGreaterThan(2000, mb_strlen($scrubbed['v']));
    }

    public function testSafeJsonTruncatesLists(): void
    {
        $items = [];
        for ($i = 0; $i < 20; $i++) {
            $items[] = str_repeat('x', 60) . $i;
        }

        $json = Scrubber::safeJson($items, 300);
        $data = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertLessThanOrEqual(300, strlen($json));

        if (array_key_exists('_truncated', $data)) {
            $this->assertTrue($data['_truncated']);
            $this->assertSame(20, $data['_total'] ?? null);
            $this->assertTrue($data['_tail'] ?? false);
            $this->assertArrayHasKey('items', $data);
        } else {
            $this->assertSame(array_keys($data), range(0, count($data) - 1));
        }
    }

    public function testSafeJsonFallsBackForLargeObjects(): void
    {
        $payload = [
            'a' => str_repeat('y', 5000),
            'b' => str_repeat('z', 5000),
        ];

        $json = Scrubber::safeJson($payload, 120);
        $data = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertTrue($data['_truncated'] ?? false);
        $this->assertArrayHasKey('_keys', $data);
        $this->assertContains('a', $data['_keys']);
    }
}
