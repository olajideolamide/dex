<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dex\Services\Core;

use Dex\Support\DexTime;
use Throwable;

/**
 * Builds the canonical request lifecycle payload while telemetry is collected.
 */
final class RequestLifecycleService
{
    private ?array $ctx = null;

    public function __construct(
        private readonly object $config,
    ) {
    }

    public function setContext(?array &$ctx): void
    {
        $this->ctx = &$ctx;
    }

    public function checkpoint(string $name, string $label, array $metadata = [], string $source = 'system'): void
    {
        $this->appendCheckpointAt($this->msOffset(), $name, $label, $metadata, $source);
    }

    public function startSpan(
        string $name,
        ?string $label = null,
        array $metadata = [],
        string $source = 'manual',
        string $origin = 'manual'
    ): ?string {
        if (! $this->ensureLifecycle()) {
            return null;
        }

        $spanId = $this->makeId('spn');
        $parentId = $this->activeSpanId();
        $depth = $this->depthForParent($parentId);

        $item = [
            'id' => $spanId,
            'type' => 'span',
            'name' => $name,
            'label' => $label ?? $name,
            'source' => $source,
            'origin' => $origin,
            'start_ms' => $this->msOffset(),
            'end_ms' => null,
            'duration_ms' => null,
            'parent_id' => $parentId,
            'depth' => $depth,
            'status' => 'open',
            'data' => $this->smallMetadata($metadata),
        ];

        if ($this->appendItem($item) === null) {
            return null;
        }

        $this->ctx['lifecycle']['open_span_stack'][] = $spanId;

        return $spanId;
    }

    public function finishSpan(?string $id, string $status = 'ok', array $metadata = []): void
    {
        if (! $this->ctx || $id === null || $id === '') {
            return;
        }

        $this->popSpanStack($id);

        $index = $this->itemIndex($id);
        if ($index === null) {
            return;
        }

        $endMs = $this->msOffset();
        $startMs = (float) ($this->ctx['lifecycle']['items'][$index]['start_ms'] ?? 0);
        $currentStatus = (string) ($this->ctx['lifecycle']['items'][$index]['status'] ?? 'open');

        $this->ctx['lifecycle']['items'][$index]['end_ms'] = $endMs;
        $this->ctx['lifecycle']['items'][$index]['duration_ms'] = max(0.0, round($endMs - $startMs, 3));
        $this->ctx['lifecycle']['items'][$index]['status'] = $currentStatus === 'failed' ? 'failed' : $status;

        if ($metadata !== []) {
            $existingMetadata = $this->ctx['lifecycle']['items'][$index]['data'] ?? [];
            $this->ctx['lifecycle']['items'][$index]['data'] = $this->smallMetadata(
                array_merge(is_array($existingMetadata) ? $existingMetadata : [], $metadata)
            );
        }
    }

    public function breadcrumb(
        string $category,
        string $message,
        array $metadata = [],
        string $level = 'info',
        string $origin = 'manual'
    ): void {
        if (! $this->ensureLifecycle()) {
            return;
        }

        $parentId = $this->activeSpanId();
        $source = $origin === 'manual' ? 'manual' : $this->sourceFromCategory($category);
        $breadcrumbData = array_merge($metadata, [
            'category' => $category,
            'level' => $level,
        ]);

        $this->appendItem([
            'id' => $this->makeId('brd'),
            'type' => 'breadcrumb',
            'name' => $category !== '' ? 'breadcrumb.' . $category : 'breadcrumb',
            'label' => $message,
            'source' => $source,
            'origin' => $origin,
            'start_ms' => $this->msOffset(),
            'end_ms' => null,
            'duration_ms' => null,
            'parent_id' => $parentId,
            'depth' => $this->depthForParent($parentId),
            'status' => $this->statusFromLevel($level),
            'data' => $this->smallMetadata($breadcrumbData),
        ]);
    }

    public function dbQuery(string $sql, float $durationMs, int $storedDurationMs): void
    {
        if (! $this->ensureLifecycle()) {
            return;
        }

        $durationMs = max(0.0, round($durationMs, 3));
        $isSlow = $storedDurationMs >= (int) ($this->config->slowQueryMs ?? 100);

        if ($isSlow) {
            $this->ctx['slow_query_count'] = (int) ($this->ctx['slow_query_count'] ?? 0) + 1;
            $this->ctx['slowest_query_ms'] = max(
                (int) ($this->ctx['slowest_query_ms'] ?? 0),
                $storedDurationMs
            );
        }

        $endMs = $this->msOffset();
        $startMs = max(0.0, round($endMs - $durationMs, 3));
        $parentId = $this->activeSpanId();
        $queryData = [];
        if ($sql !== '') {
            $queryData['sql'] = $this->trimString($sql, (int) ($this->config->maxSqlLength ?? 4000));
        }

        $this->appendItem([
            'id' => $this->makeId('qry'),
            'type' => 'db_query',
            'name' => 'db.query',
            'label' => 'DB query',
            'source' => 'db',
            'origin' => 'auto',
            'start_ms' => $startMs,
            'end_ms' => $endMs,
            'duration_ms' => $durationMs,
            'parent_id' => $parentId,
            'depth' => $this->depthForParent($parentId),
            'status' => 'ok',
            'data' => $this->smallMetadata($queryData),
        ]);
    }

    public function exception(Throwable $exception, array $metadata = []): void
    {
        if (! $this->ensureLifecycle()) {
            return;
        }

        $this->markActiveSpanFailed();

        $parentId = $this->activeSpanId();
        $exceptionMs = $this->msOffset();
        $this->ctx['lifecycle']['ended_at_ms'] ??= $exceptionMs;
        $exceptionData = array_merge($metadata, [
            'class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
        ]);

        $this->appendItem([
            'id' => $this->makeId('exc'),
            'type' => 'exception',
            'name' => get_class($exception),
            'label' => $exception->getMessage() !== '' ? $exception->getMessage() : get_class($exception),
            'source' => 'exception',
            'origin' => 'auto',
            'start_ms' => $exceptionMs,
            'end_ms' => null,
            'duration_ms' => null,
            'parent_id' => $parentId,
            'depth' => $this->depthForParent($parentId),
            'status' => 'failed',
            'data' => $this->smallMetadata($exceptionData),
        ]);
    }

    public function finalize(int $durationMs, int $statusCode, int $memoryPeakBytes): array
    {
        if (! $this->ensureLifecycle()) {
            return $this->emptyPayload($durationMs, $statusCode, $memoryPeakBytes);
        }

        $durationMs = max(0, $durationMs);
        $finalStatus = ($statusCode >= 500 || (bool) ($this->ctx['had_error'] ?? false)) ? 'failed' : 'ok';

        if (empty($this->ctx['lifecycle']['finalized'])) {
            $endAtMs = $this->endAtMs($durationMs);
            $this->closeOpenSpans($endAtMs, $finalStatus);

            if (! $this->hasEndedAtException()) {
                $this->appendResponseItem($durationMs, $statusCode);
                $this->appendCheckpointAt($durationMs, 'request.finished', 'Request finished', [
                    'duration_ms' => $durationMs,
                    'db_time_ms' => (int) round((float) ($this->ctx['db_time_ms'] ?? 0)),
                    'memory_peak_bytes' => $memoryPeakBytes,
                ], 'system');
            }
            $this->ctx['lifecycle']['finalized'] = true;
        }

        $items = $this->persistedItems();
        $summary = $this->summary();

        return [
            'version' => 2,
            'request' => [
                'request_id' => $this->ctx['request_id'] ?? null,
                'method' => $this->ctx['method'] ?? null,
                'path' => $this->ctx['path'] ?? null,
                'status_code' => $statusCode,
                'duration_ms' => $durationMs,
                'memory_peak_bytes' => $memoryPeakBytes,
                'created_at' => $this->ctx['_created_at'] ?? $this->ctx['started_at_dt'] ?? DexTime::nowUtcString(),
            ],
            'route' => [
                'matched' => (bool) (($this->ctx['route'] ?? null) || ($this->ctx['controller'] ?? null)),
                'route' => $this->ctx['route'] ?? null,
                'params' => $this->ctx['route_params'] ?? [],
                'controller' => $this->ctx['controller'] ?? null,
                'action' => $this->ctx['action'] ?? null,
            ],
            'summary' => $summary,
            'items' => $items,
            'hints' => [],
        ];
    }

    private function appendCheckpointAt(
        float|int $startMs,
        string $name,
        string $label,
        array $metadata,
        string $source
    ): void {
        if (! $this->ensureLifecycle()) {
            return;
        }

        $parentId = $this->activeSpanId();

        $this->appendItem([
            'id' => $this->makeId('evt'),
            'type' => 'checkpoint',
            'name' => $name,
            'label' => $label,
            'source' => $source,
            'origin' => 'auto',
            'start_ms' => max(0.0, round((float) $startMs, 3)),
            'end_ms' => null,
            'duration_ms' => null,
            'parent_id' => $parentId,
            'depth' => $this->depthForParent($parentId),
            'status' => 'ok',
            'data' => $this->smallMetadata($metadata),
        ]);
    }

    private function appendResponseItem(int $durationMs, int $statusCode): void
    {
        $this->appendItem([
            'id' => $this->makeId('rsp'),
            'type' => 'response',
            'name' => 'response.generated',
            'label' => 'Response generated',
            'source' => 'system',
            'origin' => 'auto',
            'start_ms' => $durationMs,
            'end_ms' => null,
            'duration_ms' => null,
            'parent_id' => null,
            'depth' => 0,
            'status' => $statusCode >= 500 ? 'failed' : 'ok',
            'data' => [
                'status_code' => $statusCode,
            ],
        ]);
    }

    private function appendItem(array $item): ?string
    {
        if (! $this->ensureLifecycle()) {
            return null;
        }

        if ($this->hasEndedAtException()) {
            $endedAt = $this->endedAtMs();
            if ($endedAt !== null && is_numeric($item['start_ms'] ?? null) && (float) $item['start_ms'] > $endedAt) {
                return null;
            }
        }

        $maxItems = (int) ($this->config->maxLifecycleItems ?? 220);
        if ($maxItems > 0 && count($this->ctx['lifecycle']['items']) >= $maxItems) {
            $this->ctx['lifecycle']['summary']['truncated'] = true;
            return null;
        }

        $item['data'] = $this->smallMetadata(is_array($item['data'] ?? null) ? $item['data'] : []);

        $index = count($this->ctx['lifecycle']['items']);
        $this->ctx['lifecycle']['items'][] = $item;
        $this->ctx['lifecycle']['index_by_id'][(string) $item['id']] = $index;
        $this->incrementSummary($item);

        return (string) $item['id'];
    }

    private function ensureLifecycle(): bool
    {
        if (! $this->ctx || ! ($this->config->captureLifecycle ?? true)) {
            return false;
        }

        $this->ctx['lifecycle'] ??= [];
        $this->ctx['lifecycle']['version'] = 2;
        $this->ctx['lifecycle']['items'] ??= [];
        $this->ctx['lifecycle']['index_by_id'] ??= [];
        $this->ctx['lifecycle']['open_span_stack'] ??= [];
        $this->ctx['lifecycle']['next_item_seq'] ??= 0;
        $this->ctx['lifecycle']['summary'] ??= [];
        $this->ctx['lifecycle']['ended_at_ms'] ??= null;

        foreach ($this->defaultSummary() as $key => $value) {
            $this->ctx['lifecycle']['summary'][$key] ??= $value;
        }

        return true;
    }

    private function hasEndedAtException(): bool
    {
        return $this->endedAtMs() !== null;
    }

    private function endedAtMs(): ?float
    {
        $endedAt = $this->ctx['lifecycle']['ended_at_ms'] ?? null;
        if (! is_numeric($endedAt)) {
            return null;
        }

        $endedAt = (float) $endedAt;

        return $endedAt >= 0 ? $endedAt : null;
    }

    private function endAtMs(int $durationMs): int
    {
        $endedAt = $this->endedAtMs();
        if ($endedAt === null) {
            return $durationMs;
        }

        return max(0, min($durationMs, (int) round($endedAt)));
    }

    private function defaultSummary(): array
    {
        return [
            'event_count' => 0,
            'span_count' => 0,
            'manual_span_count' => 0,
            'breadcrumb_count' => 0,
            'db_query_count' => 0,
            'exception_count' => 0,
            'max_depth' => 0,
            'truncated' => false,
        ];
    }

    private function incrementSummary(array $item): void
    {
        $summary = &$this->ctx['lifecycle']['summary'];
        $summary['event_count'] = (int) ($summary['event_count'] ?? 0) + 1;
        $summary['max_depth'] = max((int) ($summary['max_depth'] ?? 0), (int) ($item['depth'] ?? 0));

        $type = (string) ($item['type'] ?? '');
        if ($type === 'span') {
            $summary['span_count'] = (int) ($summary['span_count'] ?? 0) + 1;
            if (($item['origin'] ?? '') === 'manual') {
                $summary['manual_span_count'] = (int) ($summary['manual_span_count'] ?? 0) + 1;
            }
            return;
        }

        if ($type === 'breadcrumb') {
            $summary['breadcrumb_count'] = (int) ($summary['breadcrumb_count'] ?? 0) + 1;
            return;
        }

        if ($type === 'db_query') {
            $summary['db_query_count'] = (int) ($summary['db_query_count'] ?? 0) + 1;
            return;
        }

        if ($type === 'exception') {
            $summary['exception_count'] = (int) ($summary['exception_count'] ?? 0) + 1;
        }
    }

    private function summary(): array
    {
        $summary = array_merge($this->defaultSummary(), (array) ($this->ctx['lifecycle']['summary'] ?? []));

        return array_merge($summary, [
            'db_time_ms' => (int) round((float) ($this->ctx['db_time_ms'] ?? 0)),
            'slow_query_count' => (int) ($this->ctx['slow_query_count'] ?? 0),
            'slowest_query_ms' => (int) ($this->ctx['slowest_query_ms'] ?? 0),
        ]);
    }

    private function closeOpenSpans(int $durationMs, string $status): void
    {
        $openSpanIds = array_reverse((array) ($this->ctx['lifecycle']['open_span_stack'] ?? []));
        $this->ctx['lifecycle']['open_span_stack'] = [];

        $httpSpanContext = is_array($this->ctx['_http_span_context'] ?? null)
            ? (array) $this->ctx['_http_span_context']
            : null;

        foreach ($openSpanIds as $spanId) {
            $index = $this->itemIndex((string) $spanId);
            if ($index === null) {
                continue;
            }

            $item = &$this->ctx['lifecycle']['items'][$index];
            if (($item['type'] ?? '') !== 'span') {
                continue;
            }

            if ($httpSpanContext !== null && ($item['name'] ?? '') === 'http.server' && empty($item['parent_id'])) {
                $existingMetadata = is_array($item['data'] ?? null) ? (array) $item['data'] : [];
                $item['data'] = $this->smallMetadata(array_merge($existingMetadata, $httpSpanContext));
                unset($this->ctx['_http_span_context']);
                $httpSpanContext = null;
            }

            $startMs = (float) ($item['start_ms'] ?? 0);
            $item['end_ms'] = $durationMs;
            $item['duration_ms'] = max(0.0, round($durationMs - $startMs, 3));
            $item['status'] = ($item['status'] ?? '') === 'failed' ? 'failed' : $status;
        }
    }

    private function popSpanStack(string $id): void
    {
        if (! is_array($this->ctx['lifecycle']['open_span_stack'] ?? null)) {
            return;
        }

        while ($this->ctx['lifecycle']['open_span_stack'] !== []) {
            $top = array_pop($this->ctx['lifecycle']['open_span_stack']);
            if ($top === $id) {
                return;
            }
        }
    }

    private function markActiveSpanFailed(): void
    {
        $activeSpanId = $this->activeSpanId();
        if ($activeSpanId === null) {
            return;
        }

        $index = $this->itemIndex($activeSpanId);
        if ($index !== null) {
            $this->ctx['lifecycle']['items'][$index]['status'] = 'failed';
        }
    }

    private function activeSpanId(): ?string
    {
        $stack = $this->ctx['lifecycle']['open_span_stack'] ?? [];
        if (! is_array($stack) || $stack === []) {
            return null;
        }

        $spanId = end($stack);

        return is_string($spanId) && $spanId !== '' ? $spanId : null;
    }

    private function depthForParent(?string $parentId): int
    {
        if ($parentId === null) {
            return 0;
        }

        $index = $this->itemIndex($parentId);
        if ($index === null) {
            return 0;
        }

        return min(12, (int) ($this->ctx['lifecycle']['items'][$index]['depth'] ?? 0) + 1);
    }

    private function itemIndex(string $id): ?int
    {
        $index = $this->ctx['lifecycle']['index_by_id'][$id] ?? null;

        return is_int($index) ? $index : (is_numeric($index) ? (int) $index : null);
    }

    private function persistedItems(): array
    {
        $items = array_values(array_filter((array) ($this->ctx['lifecycle']['items'] ?? []), 'is_array'));

        foreach ($items as $index => $item) {
            $items[$index] = $this->preparePersistedItem($item);
        }

        return $items;
    }

    private function preparePersistedItem(array $item): array
    {
        if (($item['type'] ?? '') !== 'db_query') {
            return $item;
        }

        $item['data'] = $this->preparePersistedDbQueryData(is_array($item['data'] ?? null) ? $item['data'] : []);

        return $item;
    }

    private function preparePersistedDbQueryData(array $data): array
    {
        if (! isset($data['sql']) || ! is_string($data['sql']) || $data['sql'] === '') {
            return $data;
        }

        $data['sql'] = $this->trimString(
            $this->redactSql($data['sql']),
            (int) ($this->config->maxSqlLength ?? 4000)
        );

        return $data;
    }

    private function smallMetadata(array $metadata): array
    {
        $small = [];
        $approxBytes = 0;
        $maxBytes = max(256, (int) ($this->config->maxLifecycleItemDataBytes ?? 6000));
        $truncated = false;

        foreach ($metadata as $key => $value) {
            if (count($small) >= 24) {
                $truncated = true;
                break;
            }

            $normalizedKey = (string) $key;
            $normalizedValue = $this->normalizeMetadataValue($value);
            $approxBytes += strlen($normalizedKey) + $this->approxMetadataBytes($normalizedValue);

            if ($approxBytes > $maxBytes) {
                $truncated = true;
                break;
            }

            $small[$normalizedKey] = $normalizedValue;
        }

        if ($truncated || count($small) < count($metadata)) {
            $small['_truncated'] = true;
        }

        return $small;
    }

    private function normalizeMetadataValue(mixed $value): mixed
    {
        if ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            return $this->trimString($value, 500);
        }

        if (! is_array($value)) {
            return '[complex value truncated]';
        }

        $normalized = [];
        $truncated = false;

        foreach ($value as $key => $item) {
            if (count($normalized) >= 12) {
                $truncated = true;
                break;
            }

            $normalized[(string) $key] = is_scalar($item) || $item === null
                ? (is_string($item) ? $this->trimString($item, 200) : $item)
                : '[complex value truncated]';
        }

        if ($truncated || count($normalized) < count($value)) {
            $normalized['_truncated'] = true;
        }

        return $normalized;
    }

    private function approxMetadataBytes(mixed $value): int
    {
        if ($value === null) {
            return 4;
        }

        if (is_bool($value)) {
            return 5;
        }

        if (is_int($value) || is_float($value)) {
            return 24;
        }

        if (is_string($value)) {
            return strlen($value);
        }

        if (! is_array($value)) {
            return 32;
        }

        $bytes = 2;
        foreach ($value as $key => $item) {
            $bytes += strlen((string) $key) + $this->approxMetadataBytes($item);
        }

        return $bytes;
    }

    private function sourceFromCategory(string $category): string
    {
        $category = strtolower($category);
        if (str_starts_with($category, 'ci')) {
            return 'ci';
        }
        if (str_starts_with($category, 'db')) {
            return 'db';
        }

        return 'system';
    }

    private function statusFromLevel(string $level): string
    {
        return in_array(strtolower($level), ['error', 'critical', 'alert', 'emergency'], true) ? 'failed' : 'ok';
    }

    private function msOffset(): float
    {
        if (! $this->ctx) {
            return 0.0;
        }

        return max(0.0, round((microtime(true) - (float) $this->ctx['started_at']) * 1000, 3));
    }

    private function makeId(string $prefix): string
    {
        $this->ctx['lifecycle']['next_item_seq'] = (int) ($this->ctx['lifecycle']['next_item_seq'] ?? 0) + 1;

        return $prefix . '_' . $this->ctx['lifecycle']['next_item_seq'];
    }

    private function redactSql(string $sql): string
    {
        $sql = preg_replace("/'(?:''|[^'])*'/", "'?'", $sql) ?? $sql;
        $sql = preg_replace('/"(?:""|[^"])*"/', '"?"', $sql) ?? $sql;

        return preg_replace('/\b\d+(?:\.\d+)?\b/', '?', $sql) ?? $sql;
    }

    private function trimString(string $value, int $maxLength): string
    {
        if ($maxLength <= 0 || mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_substr($value, 0, $maxLength) . '…';
    }

    private function emptyPayload(int $durationMs, int $statusCode, int $memoryPeakBytes): array
    {
        return [
            'version' => 2,
            'request' => [
                'status_code' => $statusCode,
                'duration_ms' => max(0, $durationMs),
                'memory_peak_bytes' => $memoryPeakBytes,
            ],
            'route' => [],
            'summary' => $this->defaultSummary(),
            'items' => [],
            'hints' => [],
        ];
    }
}
