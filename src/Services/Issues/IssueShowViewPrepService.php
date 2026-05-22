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

namespace Dex\Services\Issues;

/**
 * Prepares/normalizes view data for the issue show page.
 *
 * NOTE: This intentionally does NOT call CI4 HTTP helpers like site_url().
 * The view layer can generate URLs.
 */
final class IssueShowViewPrepService
{
    /**
     * @param array $issue
     * @param array $occurrences
     * @param array|null $selected
     * @param array|null $requestRow
     * @return array Prepared view data (safe defaults for partials)
     */
    public function prepare(
        array $issue,
        array $occurrences,
        ?array $selected,
        ?array $requestRow,
    ): array {
        $selected = is_array($selected) ? $selected : [];

        $selectedId = (int) ($selected['id'] ?? 0);
        $selectedPos = null;
        if ($selectedId > 0 && !empty($occurrences)) {
            foreach ($occurrences as $idx => $occ) {
                if ((int) ($occ['id'] ?? 0) === $selectedId) {
                    $selectedPos = $idx;
                    break;
                }
            }
        }

        $eventNo = ($selectedPos !== null) ? ((int) $selectedPos + 1) : 1;

        $ctxRaw = $selected['context'] ?? null;
        $ctx = [];
        if (is_string($ctxRaw) && $ctxRaw !== '') {
            $decoded = json_decode($ctxRaw, true);
            $ctx = is_array($decoded) ? $decoded : [];
        } elseif (is_array($ctxRaw)) {
            $ctx = $ctxRaw;
        }

        $reqSnap = is_array($ctx['request'] ?? null) ? $ctx['request'] : [];
        $http = is_array($ctx['http'] ?? null) ? $ctx['http'] : [];
        $tags = is_array($ctx['tags'] ?? null) ? $ctx['tags'] : [];
        $exc = is_array($ctx['exception'] ?? null) ? $ctx['exception'] : [];

        $requestRow = is_array($requestRow) ? $requestRow : null;
        $requestSnapshot = $this->decodeJsonArray($requestRow['snapshot_json'] ?? null);
        $requestLifecycle = $this->decodeJsonArray($requestRow['lifecycle_json'] ?? null);

        $snapshotRequest = is_array($requestSnapshot['request'] ?? null) ? $requestSnapshot['request'] : [];
        $snapshotRouting = is_array($requestSnapshot['routing'] ?? null) ? $requestSnapshot['routing'] : [];
        $snapshotResponse = is_array($requestSnapshot['response'] ?? null) ? $requestSnapshot['response'] : [];
        $snapshotMetrics = is_array($requestSnapshot['metrics'] ?? null) ? $requestSnapshot['metrics'] : [];
        $snapshotCi = is_array($requestSnapshot['ci'] ?? null) ? $requestSnapshot['ci'] : [];
        $snapshotUser = is_array($requestSnapshot['user'] ?? null) ? $requestSnapshot['user'] : [];
        $snapshotServer = is_array($requestSnapshot['server'] ?? null) ? $requestSnapshot['server'] : [];
        $snapshotPhp = is_array($snapshotServer['php'] ?? null) ? $snapshotServer['php'] : [];
        $snapshotWebserver = is_array($snapshotServer['webserver'] ?? null) ? $snapshotServer['webserver'] : [];
        $snapshotOs = is_array($snapshotServer['os'] ?? null) ? $snapshotServer['os'] : [];
        $snapshotKernel = is_array($snapshotServer['kernel'] ?? null) ? $snapshotServer['kernel'] : [];

        $rid = (string) (
            $selected['request_id']
            ?? $requestSnapshot['request_id']
            ?? $reqSnap['request_id']
            ?? ''
        );

        $reqSnap = [
            'request_id' => $rid,
            'method' => $snapshotRequest['method'] ?? $requestRow['method'] ?? $reqSnap['method'] ?? $http['method'] ?? null,
            'path' => $snapshotRequest['path'] ?? $requestRow['path'] ?? $reqSnap['path'] ?? $http['path'] ?? null,
            'url' => $snapshotRequest['url'] ?? $http['url'] ?? null,
            'query' => $snapshotRequest['query'] ?? $http['query'] ?? null,
            'host' => $snapshotRequest['host'] ?? null,
            'scheme' => $snapshotRequest['scheme'] ?? null,
            'ip' => $snapshotUser['ip'] ?? $snapshotRequest['ip'] ?? $reqSnap['ip'] ?? null,
            'user_agent' => $snapshotUser['user_agent'] ?? $snapshotRequest['user_agent'] ?? $reqSnap['user_agent'] ?? null,
            'controller' => $snapshotRouting['controller'] ?? $requestRow['controller'] ?? $reqSnap['controller'] ?? null,
            'action' => $snapshotRouting['action'] ?? $requestRow['action'] ?? $reqSnap['action'] ?? null,
            'route' => $snapshotRouting['route'] ?? $reqSnap['route'] ?? null,
            'db_count' => $snapshotMetrics['db_count'] ?? $requestRow['db_count'] ?? $reqSnap['db_count'] ?? null,
            'db_time_ms' => $snapshotMetrics['db_time_ms'] ?? $requestRow['db_time_ms'] ?? $reqSnap['db_time_ms'] ?? null,
        ];

        $httpHeaders = [];
        if (is_array($requestSnapshot['headers'] ?? null)) {
            $httpHeaders = (array) $requestSnapshot['headers'];
        } elseif (is_array($http['headers'] ?? null)) {
            $httpHeaders = (array) $http['headers'];
        }

        $http = array_filter([
            'method' => $reqSnap['method'] ?? null,
            'path' => $reqSnap['path'] ?? null,
            'url' => $reqSnap['url'] ?? null,
            'query' => $reqSnap['query'] ?? null,
            'headers' => $httpHeaders !== [] ? $httpHeaders : null,
        ], static fn(mixed $value): bool => $value !== null && $value !== '');

        $status = $requestRow['status_code'] ?? $snapshotResponse['status_code'] ?? null;
        $durMs = $requestRow['duration_ms'] ?? $snapshotMetrics['duration_ms'] ?? null;
        $dbCnt = $requestRow['db_count'] ?? $snapshotMetrics['db_count'] ?? $reqSnap['db_count'] ?? null;
        $dbMs = $requestRow['db_time_ms'] ?? $snapshotMetrics['db_time_ms'] ?? $reqSnap['db_time_ms'] ?? null;
        $memPk = $requestRow['mem_peak'] ?? $snapshotMetrics['mem_peak'] ?? null;

        $env = (string) ($snapshotCi['env'] ?? $tags['environment'] ?? ($issue['environment'] ?? ''));
        $phpVer = (string) ($snapshotCi['php'] ?? $tags['php'] ?? '');
        $sapi = (string) ($snapshotPhp['sapi'] ?? $tags['sapi'] ?? ($snapshotCi['sapi'] ?? ''));

        if ($env !== '' && empty($tags['environment'])) {
            $tags['environment'] = $env;
        }
        if ($phpVer !== '' && empty($tags['php'])) {
            $tags['php'] = $phpVer;
        }
        if ($sapi !== '' && empty($tags['sapi'])) {
            $tags['sapi'] = $sapi;
        }
        if (!empty($reqSnap['controller']) && empty($tags['controller'])) {
            $tags['controller'] = (string) $reqSnap['controller'];
        }
        if (!empty($reqSnap['action']) && empty($tags['action'])) {
            $tags['action'] = (string) $reqSnap['action'];
        }
        if (!empty($reqSnap['method']) && empty($tags['method'])) {
            $tags['method'] = (string) $reqSnap['method'];
        }

        $controller = (string) ($reqSnap['controller'] ?? '');
        $action = (string) ($reqSnap['action'] ?? '');

        $method = (string) ($reqSnap['method'] ?? '');
        $path = (string) ($reqSnap['path'] ?? '');
        $fullUrl = (string) ($reqSnap['url'] ?? '');
        $query = $this->normalizeQuery($reqSnap['query'] ?? null);

        $ip = (string) ($reqSnap['ip'] ?? '');
        $ua = (string) ($reqSnap['user_agent'] ?? '');
        $uaParts = $this->parseUserAgent($ua);
        if (!empty($snapshotUser['browser'])) {
            $uaParts['browser'] = (string) $snapshotUser['browser'];
        }
        if (!empty($snapshotUser['browser_version'])) {
            $uaParts['browser_version'] = (string) $snapshotUser['browser_version'];
        }
        if (!empty($snapshotUser['os'])) {
            $uaParts['os'] = (string) $snapshotUser['os'];
        }
        if (!empty($snapshotUser['device'])) {
            $uaParts['device'] = (string) $snapshotUser['device'];
        }
        if (array_key_exists('is_bot', $snapshotUser)) {
            $uaParts['is_bot'] = (bool) $snapshotUser['is_bot'];
        }

        $userCountry = (string) ($snapshotUser['country'] ?? '');
        $userContextRows = $this->rows([
            ['Country', $userCountry],
            ['Browser', $uaParts['browser'] ?? null],
            ['Browser version', $uaParts['browser_version'] ?? null],
            ['OS', $uaParts['os'] ?? null],
            ['Device', $uaParts['device'] ?? null],
            ['Bot', !empty($uaParts['is_bot']) ? 'Yes' : null],
            ['User agent', $ua, true, $ua],
        ]);

        $httpHeaderRows = [];
        foreach ($httpHeaders as $headerName => $headerValue) {
            $httpHeaderRows[] = [
                'k' => (string) $headerName,
                'v' => (string) $headerValue,
                'mono' => true,
                'copy' => (string) $headerValue,
            ];
        }

        $codeIgniterRows = $this->rows([
            ['Environment', $env],
            ['Version', $snapshotCi['version'] ?? null],
            ['Timezone', $snapshotCi['timezone'] ?? null],
        ]);
        $phpRows = $this->rows([
            ['Version', $snapshotPhp['version'] ?? $phpVer],
            ['SAPI', $snapshotPhp['sapi'] ?? $sapi],
            ['Interface', $snapshotPhp['interface'] ?? null],
            ['Memory limit', $snapshotPhp['memory_limit'] ?? null],
            ['Max execution time', $snapshotPhp['max_execution_time'] ?? null],
            ['Upload max filesize', $snapshotPhp['upload_max_filesize'] ?? null],
            ['Post max size', $snapshotPhp['post_max_size'] ?? null],
        ]);
        $webServerRows = $this->rows([
            ['Software', $snapshotWebserver['software'] ?? null],
            ['Gateway', $snapshotWebserver['gateway_interface'] ?? null],
            ['Protocol', $snapshotWebserver['protocol'] ?? null],
            ['Server name', $snapshotWebserver['server_name'] ?? null],
            ['Server port', $snapshotWebserver['server_port'] ?? null],
        ]);
        $osRows = $this->rows([
            ['OS family', $snapshotOs['family'] ?? null],
            ['OS', $snapshotOs['name'] ?? null],
            ['Machine', $snapshotOs['machine'] ?? null],
            ['Kernel', $snapshotKernel['name'] ?? null],
            ['Kernel release', $snapshotKernel['release'] ?? null],
            ['Kernel version', $snapshotKernel['version'] ?? null],
        ]);
        $serverContextSections = [
            'CodeIgniter' => $codeIgniterRows,
            'PHP' => $phpRows,
            'Web Server' => $webServerRows,
            'OS / Kernel' => $osRows,
        ];

        $hasReq = is_array($requestRow);
        $hasHttpHeaders = !empty($httpHeaders);

        $statusClass = $this->statusBadgeClass($status);
        $durClass = $this->durationBadgeClass($durMs);

        $frames = $this->normalizeFrames($ctx, $exc);
        $lifecycle = $this->normalizeLifecycle($requestLifecycle);
        $lifecycleItems = $this->displayLifecycleItems($lifecycle);
        $lifecycleSummaryRows = $this->lifecycleSummaryRows($lifecycle);
        $lifecycleHints = $this->normalizeLifecycleHints($lifecycle['hints'] ?? []);

        $culprit = null;
        if (!empty($frames) && is_array($frames[0] ?? null)) {
            $culprit = $frames[0];
        } elseif (!empty($exc)) {
            $culprit = [
                'file' => (string) ($exc['file'] ?? ''),
                'line' => (int) ($exc['line'] ?? 0),
                'fn' => (string) ($exc['class'] ?? ''),
            ];
        }

        return [
            // Core rows
            'issue' => $issue,
            'occurrences' => $occurrences,
            'selected' => $selected,
            'requestRow' => $requestRow,

            // Normalized context
            'ctx' => $ctx,
            'reqSnap' => $reqSnap,
            'http' => $http,
            'tags' => $tags,
            'exc' => $exc,

            // Derived fields used by partials
            'selectedId' => $selectedId,
            'eventNo' => $eventNo,
            'rid' => $rid,
            'method' => $method,
            'path' => $path,
            'fullUrl' => $fullUrl,
            'query' => $query,
            'status' => $status,
            'durMs' => $durMs,
            'dbCnt' => $dbCnt,
            'dbMs' => $dbMs,
            'memPk' => $memPk,
            'env' => $env,
            'phpVer' => $phpVer,
            'sapi' => $sapi,
            'controller' => $controller,
            'action' => $action,
            'ip' => $ip,
            'ua' => $ua,
            'uaParts' => $uaParts,
            'userCountry' => $userCountry,
            'userContextRows' => $userContextRows,
            'httpHeaderRows' => $httpHeaderRows,
            'codeIgniterRows' => $codeIgniterRows,
            'phpRows' => $phpRows,
            'webServerRows' => $webServerRows,
            'osRows' => $osRows,
            'serverContextSections' => $serverContextSections,
            'hasReq' => $hasReq,
            'hasHttpHeaders' => $hasHttpHeaders,
            'statusClass' => $statusClass,
            'durClass' => $durClass,
            'frames' => $frames,
            'lifecycle' => $lifecycle,
            'lifecycleItems' => $lifecycleItems,
            'lifecycleSummaryRows' => $lifecycleSummaryRows,
            'lifecycleHints' => $lifecycleHints,
            'culprit' => $culprit,
        ];
    }

    private function normalizeQuery(mixed $query): string
    {
        if (is_array($query)) {
            return http_build_query($query);
        }

        return (string) ($query ?? '');
    }

    /**
     * @param array<int, array{0:string, 1:mixed, 2?:bool, 3?:string}> $rows
     * @return array<int, array{k:string, v:mixed, mono?:bool, copy?:string}>
     */
    private function rows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $value = $row[1] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $item = [
                'k' => $row[0],
                'v' => $value,
            ];
            if (($row[2] ?? false) === true) {
                $item['mono'] = true;
            }
            if (($row[3] ?? '') !== '') {
                $item['copy'] = (string) $row[3];
            }

            $out[] = $item;
        }

        return $out;
    }

    private function normalizeList(mixed $value): array
    {
        return is_array($value) ? array_values($value) : [];
    }

    private function normalizeLifecycle(array $lifecycle): array
    {
        $items = array_values(array_filter($this->normalizeList($lifecycle['items'] ?? null), 'is_array'));
        return [
            'version' => is_numeric($lifecycle['version'] ?? null) ? (int) $lifecycle['version'] : 2,
            'summary' => is_array($lifecycle['summary'] ?? null) ? $lifecycle['summary'] : [],
            'items' => $items,
            'hints' => $this->normalizeList($lifecycle['hints'] ?? null),
        ];
    }

    private function displayLifecycleItems(array $lifecycle): array
    {
        $items = array_values(array_filter($this->normalizeList($lifecycle['items'] ?? null), 'is_array'));

        $displayItems = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $durationMs = $item['duration_ms'] ?? null;
            $type = (string) ($item['type'] ?? 'checkpoint');

            $name = strtolower(trim((string) ($item['name'] ?? '')));
            $label = strtolower(trim((string) ($item['label'] ?? $item['name'] ?? '')));

            // Remove noisy duplicates in lifecycle timeline (keep the primary checkpoint/span).
            if ($type === 'breadcrumb') {
                if ($name === 'breadcrumb.request' && $label === 'request started') {
                    continue;
                }
                if ($name === 'breadcrumb.ci.event' && ($label === 'pre_system' || $label === 'pre system')) {
                    continue;
                }
                if ($name === 'breadcrumb.ci' && $label === 'controller resolved') {
                    continue;
                }
            }

            // Backward-compat: show the route match event as a span (not a checkpoint).
            if ($type === 'checkpoint' && $name === 'route.matched') {
                $type = 'span';
            }

            $metadata = is_array($item['data'] ?? null) ? $item['data'] : [];
            if ($type === 'db_query') {
                [$item, $metadata] = $this->prepareDbQueryDisplay($item, $metadata);
            }

            $depth = min(5, max(0, (int) ($item['depth'] ?? 0)));

            $displayItems[] = [
                'id' => (string) ($item['id'] ?? ''),
                'type' => $type,
                'type_label' => $this->lifecycleTypeLabel($type),
                'type_tooltip' => match ($type) {
                    'db_query' => 'Database query',
                    'breadcrumb' => 'Breadcrumb',
                    'exception' => 'Exception',
                    'response' => 'Response',
                    'span' => 'Span',
                    default => 'Checkpoint',
                },
                'type_class' => $this->lifecycleTypeClass($type),
                'name' => (string) ($item['name'] ?? ''),
                'label' => (string) ($item['label'] ?? $item['name'] ?? ''),
                'time_label' => '+' . $this->formatMs((float) ($item['start_ms'] ?? 0)),
                'duration_label' => is_numeric($durationMs) ? $this->formatMs((float) $durationMs) : '',
                'status' => (string) ($item['status'] ?? 'unknown'),
                'status_class' => $this->lifecycleStatusClass((string) ($item['status'] ?? 'unknown')),
                'depth' => $depth,
                'depth_class' => 'ps-' . $depth,
                'data_rows' => $this->lifecycleDataRows($metadata),
            ];
        }

        return $displayItems;
    }

    private function lifecycleSummaryRows(array $lifecycle): array
    {
        $summary = is_array($lifecycle['summary'] ?? null) ? $lifecycle['summary'] : [];

        return $this->rows([
            ['Events', $summary['event_count'] ?? null],
            ['Spans', $summary['span_count'] ?? null],
            ['Manual spans', $summary['manual_span_count'] ?? null],
            ['Breadcrumbs', $summary['breadcrumb_count'] ?? null],
            ['DB queries', $summary['db_query_count'] ?? null],
            ['DB time', isset($summary['db_time_ms']) ? $this->formatMs((int) $summary['db_time_ms']) : null],
            ['Slow queries', $summary['slow_query_count'] ?? null],
            ['Slowest query', isset($summary['slowest_query_ms']) ? $this->formatMs((int) $summary['slowest_query_ms']) : null],
        ]);
    }

    private function normalizeLifecycleHints(mixed $hints): array
    {
        $normalized = [];
        foreach ($this->normalizeList($hints) as $hint) {
            if (is_string($hint)) {
                $normalized[] = [
                    'level' => 'warning',
                    'message' => $hint,
                ];
                continue;
            }

            if (! is_array($hint)) {
                continue;
            }

            $message = (string) ($hint['message'] ?? '');
            if ($message === '') {
                continue;
            }

            $normalized[] = [
                'level' => (string) ($hint['level'] ?? 'warning'),
                'message' => $message,
            ];
        }

        return $normalized;
    }

    /**
     * @return array{0: array, 1: array}
     */
    private function prepareDbQueryDisplay(array $item, array $metadata): array
    {
        $sql = is_string($metadata['sql'] ?? null) ? (string) $metadata['sql'] : '';
        if ($sql === '') {
            return [$item, $metadata];
        }

        $queryType = $this->queryTypeFromSql($sql);
        $tableName = $this->tableNameFromSql($sql);

        if ($queryType !== 'query') {
            $item['name'] = 'db.query.' . $queryType;
            $metadata['query_type'] ??= $queryType;
        }

        if ($tableName !== null) {
            $metadata['table'] ??= $tableName;
        }

        $item['label'] = $this->dbQueryLabel($queryType, $tableName);

        return [$item, $metadata];
    }

    private function queryTypeFromSql(string $sql): string
    {
        if (trim($sql) === '') {
            return 'query';
        }

        $word = strtolower((string) strtok(ltrim($sql), " \t\r\n("));

        return match ($word) {
            'select', 'insert', 'update', 'delete', 'replace', 'truncate', 'alter', 'create', 'drop' => $word,
            default => 'query',
        };
    }

    private function tableNameFromSql(string $sql): ?string
    {
        if (preg_match('/\b(?:from|join|into|update)\s+`?([A-Za-z0-9_.-]+)`?/i', $sql, $matches) !== 1) {
            return null;
        }

        $table = trim($matches[1], '`');

        return $table !== '' ? $table : null;
    }

    private function dbQueryLabel(string $queryType, ?string $tableName): string
    {
        if ($queryType === 'query') {
            return 'DB query';
        }

        $label = strtoupper($queryType);

        return $tableName !== null ? $label . ' ' . $tableName : $label;
    }

    private function lifecycleDataRows(array $metadata): array
    {
        $rows = [];
        foreach ($metadata as $key => $value) {
            if (count($rows) >= 8 || $value === null || $value === '') {
                continue;
            }

            $rows[] = [
                'k' => (string) $key,
                'v' => is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ];
        }

        return $rows;
    }

    private function lifecycleTypeLabel(string $type): string
    {
        return match ($type) {
            'db_query' => 'DB',
            'breadcrumb' => 'BC',
            'exception' => 'EX',
            'response' => 'RS',
            'span' => 'SP',
            default => 'CP',
        };
    }

    private function lifecycleTypeClass(string $type): string
    {
        return match ($type) {
            'db_query' => 'dex-lifecycle-badge--db',
            'exception' => 'dex-lifecycle-badge--error',
            'response' => 'dex-lifecycle-badge--response',
            'span' => 'dex-lifecycle-badge--span',
            'breadcrumb' => 'dex-lifecycle-badge--breadcrumb',
            default => 'dex-lifecycle-badge--checkpoint',
        };
    }

    private function lifecycleStatusClass(string $status): string
    {
        return match ($status) {
            'failed' => 'text-danger',
            'open' => 'text-warning',
            'cancelled' => 'text-secondary',
            default => 'text-success',
        };
    }

    private function formatMs(int|float $milliseconds): string
    {
        $milliseconds = (float) $milliseconds;
        if ($milliseconds > 0 && $milliseconds < 1) {
            return '<1 ms';
        }
        if ($milliseconds < 1000) {
            return number_format($milliseconds, 0) . ' ms';
        }

        return number_format($milliseconds / 1000, 2) . ' s';
    }

    private function decodeJsonArray(mixed $value): array
    {
        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeFrames(array $ctx, array $exc): array
    {
        $frames = $this->normalizeList($ctx['frames'] ?? null);
        if ($frames !== []) {
            return $frames;
        }

        $normalized = [];

        $topFile = (string) ($exc['file'] ?? '');
        $topLine = (int) ($exc['line'] ?? 0);
        if ($topFile !== '' || $topLine > 0) {
            $normalized[] = [
                'file' => $topFile,
                'line' => $topLine,
                'fn' => (string) ($exc['class'] ?? ''),
            ];
        }

        foreach ($this->normalizeList($exc['trace'] ?? null) as $frame) {
            if (! is_array($frame)) {
                continue;
            }

            $file = (string) ($frame['file'] ?? '');
            $line = (int) ($frame['line'] ?? 0);
            $class = (string) ($frame['class'] ?? '');
            $type = (string) ($frame['type'] ?? '');
            $function = (string) ($frame['function'] ?? '');
            $fn = trim($class . $type . $function);

            if ($file === '' && $line <= 0 && $fn === '') {
                continue;
            }

            $normalized[] = [
                'file' => $file,
                'line' => $line,
                'fn' => $fn,
            ];
        }

        return $normalized;
    }

    private function statusBadgeClass(mixed $status): string
    {
        if (!is_numeric($status)) {
            return 'secondary';
        }

        $s = (int) $status;
        if ($s >= 200 && $s <= 299) {
            return 'success';
        }
        if ($s >= 300 && $s <= 399) {
            return 'azure';
        }
        if ($s >= 400 && $s <= 499) {
            return 'warning';
        }
        if ($s >= 500) {
            return 'danger';
        }

        return 'secondary';
    }

    private function durationBadgeClass(mixed $durMs): string
    {
        if (!is_numeric($durMs)) {
            return 'secondary';
        }

        $d = (int) $durMs;
        if ($d >= 1000) {
            return 'danger';
        }
        if ($d >= 700) {
            return 'warning';
        }

        return 'success';
    }

    /**
     * Very small UA parser for breakdowns (not meant to be perfect).
     * Kept here to avoid depending on view helpers in services.
     */
    private function parseUserAgent(?string $ua): array
    {
        $ua = (string) ($ua ?? '');
        $uaL = strtolower($ua);

        $os = 'Unknown';
        if (str_contains($uaL, 'windows nt')) {
            $os = 'Windows';
        } elseif (str_contains($uaL, 'android')) {
            $os = 'Android';
        } elseif (str_contains($uaL, 'iphone') || str_contains($uaL, 'ipad') || str_contains($uaL, 'ios')) {
            $os = 'iOS';
        } elseif (str_contains($uaL, 'mac os x') || str_contains($uaL, 'macintosh')) {
            $os = 'macOS';
        } elseif (str_contains($uaL, 'linux')) {
            $os = 'Linux';
        }

        $browser = 'Unknown';
        $browserVersion = null;
        foreach (
            [
            'Edge' => '/edg\/([0-9.]+)/i',
            'Opera' => '/(?:opr|opera)\/([0-9.]+)/i',
            'Chrome' => '/chrome\/([0-9.]+)/i',
            'Safari' => '/version\/([0-9.]+).*safari\//i',
            'Firefox' => '/firefox\/([0-9.]+)/i',
            'curl' => '/curl\/([0-9.]+)/i',
            ] as $name => $pattern
        ) {
            if (preg_match($pattern, $ua, $matches) === 1) {
                $browser = $name;
                $browserVersion = $matches[1];
                break;
            }
        }

        $device = 'Desktop';
        if (str_contains($uaL, 'bot') || str_contains($uaL, 'crawler') || str_contains($uaL, 'spider')) {
            $device = 'Bot';
        } elseif (str_contains($uaL, 'ipad') || str_contains($uaL, 'tablet')) {
            $device = 'Tablet';
        } elseif (str_contains($uaL, 'mobile') || str_contains($uaL, 'iphone') || str_contains($uaL, 'android')) {
            $device = 'Mobile';
        }

        return [
            'os' => $os,
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'device' => $device,
            'is_bot' => $device === 'Bot',
        ];
    }
}
