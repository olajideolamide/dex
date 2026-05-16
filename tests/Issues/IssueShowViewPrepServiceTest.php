<?php

declare(strict_types=1);

namespace Dex\Tests\Issues;

use Dex\Services\Issues\IssueShowViewPrepService;
use PHPUnit\Framework\TestCase;

final class IssueShowViewPrepServiceTest extends TestCase
{
    public function testPrefersLifecyclePayloadWhenPresent(): void
    {
        $service = new IssueShowViewPrepService();

        $requestRow = [
            'method' => 'GET',
            'path' => '/orders',
            'status_code' => 500,
            'duration_ms' => 200,
            'db_count' => 1,
            'db_time_ms' => 75,
            'mem_peak' => 4096,
            'lifecycle_json' => json_encode([
                'version' => 2,
                'summary' => [
                    'event_count' => 2,
                    'span_count' => 1,
                    'manual_span_count' => 1,
                    'breadcrumb_count' => 0,
                    'db_query_count' => 1,
                    'db_time_ms' => 75,
                    'slow_query_count' => 1,
                    'slowest_query_ms' => 75,
                    'duplicate_query_count' => 0,
                    'n_plus_one_suspected' => false,
                ],
                'items' => [
                    [
                        'id' => 'spn_1',
                        'type' => 'span',
                        'name' => 'orders.create',
                        'label' => 'Create order',
                        'start_ms' => 5,
                        'duration_ms' => 120,
                        'depth' => 0,
                        'status' => 'failed',
                        'data' => ['order_id' => 42],
                    ],
                ],
                'hints' => [
                    [
                        'level' => 'warning',
                        'message' => 'Create order took most of the request.',
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ];

        $prepared = $service->prepare(
            ['id' => 1],
            [],
            ['id' => 10, 'context' => []],
            $requestRow,
        );

        $this->assertSame(2, $prepared['lifecycle']['version']);
        $this->assertCount(1, $prepared['lifecycleItems']);
        $this->assertSame('Create order', $prepared['lifecycleItems'][0]['label']);
        $this->assertSame('failed', $prepared['lifecycleItems'][0]['status']);
        $this->assertCount(1, $prepared['lifecycleHints']);
    }

    public function testPrefersRequestRowSnapshotAndRequestTelemetry(): void
    {
        $service = new IssueShowViewPrepService();

        $selected = [
            'id' => 99,
            'request_id' => 'req-123',
            'context' => json_encode([
                'request' => [
                    'method' => 'POST',
                    'path' => '/legacy/path',
                    'controller' => 'LegacyController',
                ],
                'http' => [
                    'url' => 'https://legacy.test/legacy/path',
                ],
                'tags' => [
                    'environment' => 'legacy',
                ],
                'exception' => [
                    'class' => 'RuntimeException',
                    'file' => 'C:/app/Services/IssueService.php',
                    'line' => 42,
                    'trace' => [
                        [
                            'file' => 'C:/app/Controllers/Home.php',
                            'line' => 17,
                            'class' => 'App\\Controllers\\Home',
                            'type' => '->',
                            'function' => 'index',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ];

        $requestRow = [
            'method' => 'GET',
            'path' => '/users',
            'status_code' => 500,
            'duration_ms' => 156,
            'db_count' => 3,
            'db_time_ms' => 12,
            'mem_peak' => 4096,
            'controller' => 'App\\Controllers\\Users',
            'action' => 'create',
            'snapshot_json' => json_encode([
                'request_id' => 'req-123',
                'ci' => [
                    'env' => 'production',
                    'php' => '8.3.4',
                    'sapi' => 'fpm-fcgi',
                    'version' => '4.6.1',
                    'timezone' => 'UTC',
                ],
                'routing' => [
                    'controller' => 'App\\Controllers\\Users',
                    'action' => 'create',
                    'route' => 'api/v1/users',
                ],
                'request' => [
                    'method' => 'PUT',
                    'path' => '/api/v1/users',
                    'url' => 'https://app.test/api/v1/users',
                    'query' => 'page=2',
                    'ip' => '198.51.100.23',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/124.0',
                ],
                'user' => [
                    'country' => 'NG',
                    'browser' => 'Chrome',
                    'browser_version' => '124.0',
                    'os' => 'Windows',
                    'device' => 'Desktop',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/124.0',
                ],
                'response' => [
                    'status_code' => 500,
                ],
                'server' => [
                    'php' => [
                        'version' => '8.3.4',
                        'sapi' => 'fpm-fcgi',
                        'memory_limit' => '128M',
                    ],
                    'webserver' => [
                        'software' => 'nginx/1.24',
                    ],
                    'os' => [
                        'family' => 'Linux',
                    ],
                    'kernel' => [
                        'release' => '6.1.0',
                    ],
                ],
                'metrics' => [
                    'duration_ms' => 144,
                    'db_count' => 5,
                    'db_time_ms' => 18,
                    'mem_peak' => 8192,
                ],
            ], JSON_THROW_ON_ERROR),
        ];

        $prepared = $service->prepare(
            ['id' => 1, 'environment' => 'production'],
            [$selected],
            $selected,
            $requestRow,
        );

        $this->assertSame('PUT', $prepared['method']);
        $this->assertSame('/api/v1/users', $prepared['path']);
        $this->assertSame('https://app.test/api/v1/users', $prepared['fullUrl']);
        $this->assertSame('page=2', $prepared['query']);
        $this->assertSame('production', $prepared['env']);
        $this->assertSame('8.3.4', $prepared['phpVer']);
        $this->assertSame('App\\Controllers\\Users', $prepared['controller']);
        $this->assertSame('create', $prepared['action']);
        $this->assertSame('C:/app/Services/IssueService.php', $prepared['culprit']['file']);
        $this->assertSame(42, $prepared['culprit']['line']);
        $this->assertCount(2, $prepared['frames']);
        $this->assertSame('Windows', $prepared['uaParts']['os']);
        $this->assertSame('Chrome', $prepared['uaParts']['browser']);
        $this->assertSame('Desktop', $prepared['uaParts']['device']);
        $this->assertSame('NG', $prepared['userCountry']);
        $this->assertNotEmpty($prepared['userContextRows']);
        $this->assertNotEmpty($prepared['codeIgniterRows']);
        $this->assertNotEmpty($prepared['phpRows']);
        $this->assertNotEmpty($prepared['webServerRows']);
        $this->assertNotEmpty($prepared['osRows']);
        $this->assertSame([], $prepared['lifecycleItems']);
    }

    public function testFallsBackToOccurrenceContextWhenRequestRowIsMissing(): void
    {
        $service = new IssueShowViewPrepService();

        $selected = [
            'id' => 100,
            'request_id' => null,
            'context' => [
                'request' => [
                    'method' => 'POST',
                    'path' => '/legacy/orders',
                    'controller' => 'LegacyOrders',
                    'action' => 'store',
                    'ip' => '203.0.113.2',
                    'user_agent' => 'curl/8.0',
                ],
                'http' => [
                    'url' => 'https://legacy.test/legacy/orders',
                    'query' => 'debug=1',
                ],
                'tags' => [
                    'environment' => 'staging',
                    'php' => '8.2.0',
                    'sapi' => 'fpm-fcgi',
                ],
                'exception' => [
                    'class' => 'LogicException',
                    'file' => 'C:/app/Legacy.php',
                    'line' => 8,
                    'trace' => [],
                ],
            ],
        ];

        $prepared = $service->prepare(
            ['id' => 2],
            [$selected],
            $selected,
            null,
        );

        $this->assertSame('POST', $prepared['method']);
        $this->assertSame('/legacy/orders', $prepared['path']);
        $this->assertSame('https://legacy.test/legacy/orders', $prepared['fullUrl']);
        $this->assertSame('debug=1', $prepared['query']);
        $this->assertSame('staging', $prepared['env']);
        $this->assertSame('8.2.0', $prepared['phpVer']);
        $this->assertSame('fpm-fcgi', $prepared['sapi']);
        $this->assertSame('LegacyOrders', $prepared['controller']);
        $this->assertSame('store', $prepared['action']);
        $this->assertSame('C:/app/Legacy.php', $prepared['culprit']['file']);
        $this->assertSame('curl', $prepared['uaParts']['browser']);
        $this->assertSame([], $prepared['lifecycleItems']);
    }
}
