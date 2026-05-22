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

namespace Dex\Support;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\IncomingRequest;
use Dex\DTO\ResponseMeta;
use Throwable;

/**
 * Item 15: CI4 request snapshot ("Debug Toolbar, but persisted")
 *
 * Goal: capture CI4-specific request context cheaply and safely,
 * only for requests we already decided to store because they are tied to an error.
 */
class RequestSnapshot
{
    /**
     * Build a scrub-friendly request snapshot for stored requests.
     *
     * @param array $ctx Dex request context
     */
    public static function build(array $ctx, object $config, ?ResponseMeta $response = null): array
    {
        $profile = strtolower((string) ($config->snapshotProfile ?? 'full'));
        if ($profile === 'minimal') {
            return self::buildMinimal($ctx, $response);
        }

        /** @var RequestInterface|null $req */
        $req = $ctx['_request'] ?? null;

        $status = $response?->statusCode;
        $contentType = $response?->headerLine('Content-Type');
        $headers = $req ? self::headers($req, $config) : [];

        $url = null;
        $query = null;
        $host = null;
        $scheme = null;
        if ($req) {
            try {
                $uri = $req->getUri();
                $url = (string) $uri;
                $query = (string) $uri->getQuery();
                $host = (string) $uri->getHost();
                $scheme = (string) $uri->getScheme();
            } catch (Throwable) {
                // ignore
            }
        }

        $ciVersion = null;
        if (class_exists('CodeIgniter\\CodeIgniter') && defined('CodeIgniter\\CodeIgniter::CI_VERSION')) {
            /** @phpstan-ignore-next-line */
            $ciVersion = \CodeIgniter\CodeIgniter::CI_VERSION;
        }

        $userAgent = (string) ($ctx['user_agent'] ?? '');
        $userAgentParts = self::parseUserAgent($userAgent);

        $snapshot = [
            'request_id' => $ctx['request_id'] ?? null,
            'ci' => [
                'env' => defined('ENVIRONMENT') ? ENVIRONMENT : null,
                'version' => $ciVersion,
                'php' => PHP_VERSION,
                'sapi' => PHP_SAPI,
                'timezone' => date_default_timezone_get(),
            ],
            'routing' => [
                'controller' => $ctx['controller'] ?? null,
                'action' => $ctx['action'] ?? null,
                'route' => $ctx['route'] ?? null,
                'params' => $ctx['route_params'] ?? null,
            ],
            'filters' => [
                // Item 14 (refined): cheap filter visibility (counts + aliases).
                'before_count' => $ctx['filters_before_count'] ?? null,
                'after_count'  => $ctx['filters_after_count'] ?? null,
                'before' => $ctx['filters_before'] ?? null,
                'after'  => $ctx['filters_after'] ?? null,
            ],
            'request' => [
                'method' => $ctx['method'] ?? null,
                'path' => $ctx['path'] ?? null,
                'url' => $url,
                'query' => $query,
                'host' => $host,
                'scheme' => $scheme,
                'ip' => $ctx['ip'] ?? null,
                'user_agent' => $ctx['user_agent'] ?? null,
                'is_ajax' => $req ? self::requestAjaxState($req) : null,
            ],
            'user' => [
                'ip' => $ctx['ip'] ?? null,
                'country' => self::countryFromHeaders($headers, $req),
                'user_agent' => $ctx['user_agent'] ?? null,
                'browser' => $userAgentParts['browser'],
                'browser_version' => $userAgentParts['browser_version'],
                'os' => $userAgentParts['os'],
                'device' => $userAgentParts['device'],
                'is_bot' => $userAgentParts['is_bot'],
            ],
            'response' => [
                'status_code' => $status,
                'content_type' => $contentType,
            ],
            'server' => self::serverInfo($req),
            'metrics' => [
                'duration_ms' => $ctx['_duration_ms'] ?? null,
                'mem_peak' => $ctx['_mem_peak'] ?? null,
                'db_count' => $ctx['db_count'] ?? null,
                'db_time_ms' => isset($ctx['db_time_ms']) ? (int) round((float) $ctx['db_time_ms']) : null,
                'breadcrumbs' => $ctx['lifecycle']['summary']['breadcrumb_count'] ?? null,
                'spans' => $ctx['lifecycle']['summary']['span_count'] ?? null,
                'lifecycle_events' => $ctx['lifecycle']['summary']['event_count'] ?? null,
            ],
            'flags' => [
                'sampled' => $ctx['_sample_hit'] ?? false,
                'slow' => $ctx['_slow_hit'] ?? null,
                'had_error' => $ctx['had_error'] ?? false,
            ],
        ];


        if (($config->snapshotIncludeInputKeys ?? true) === true && $req) {
            $maxKeys = (int) ($config->snapshotMaxKeys ?? 200);
            $snapshot['input'] = self::inputKeys($req, $maxKeys);
        }

        if ($headers !== []) {
            $snapshot['headers'] = $headers;
        }

        // Ensure scrub-fields redaction applies if used later.
        return $snapshot;
    }

    private static function buildMinimal(array $ctx, ?ResponseMeta $response = null): array
    {
        return [
            'request_id' => $ctx['request_id'] ?? null,
            'routing' => [
                'controller' => $ctx['controller'] ?? null,
                'action' => $ctx['action'] ?? null,
                'route' => $ctx['route'] ?? null,
                'params' => $ctx['route_params'] ?? null,
            ],
            'request' => [
                'method' => $ctx['method'] ?? null,
                'path' => $ctx['path'] ?? null,
                'ip' => $ctx['ip'] ?? null,
                'user_agent' => $ctx['user_agent'] ?? null,
            ],
            'response' => [
                'status_code' => $response?->statusCode,
                'content_type' => $response?->headerLine('Content-Type'),
            ],
            'metrics' => [
                'duration_ms' => $ctx['_duration_ms'] ?? null,
                'mem_peak' => $ctx['_mem_peak'] ?? null,
                'db_count' => $ctx['db_count'] ?? null,
                'db_time_ms' => isset($ctx['db_time_ms']) ? (int) round((float) $ctx['db_time_ms']) : null,
                'breadcrumbs' => $ctx['lifecycle']['summary']['breadcrumb_count'] ?? null,
                'spans' => $ctx['lifecycle']['summary']['span_count'] ?? null,
                'lifecycle_events' => $ctx['lifecycle']['summary']['event_count'] ?? null,
            ],
            'flags' => [
                'sampled' => $ctx['_sample_hit'] ?? false,
                'slow' => $ctx['_slow_hit'] ?? null,
                'had_error' => $ctx['had_error'] ?? false,
            ],
        ];
    }

    /**
     * Collect input key names (GET/POST/FILES) with a cap.
     */
    private static function inputKeys(RequestInterface $req, int $maxKeys): array
    {
        $out = [];

        $get = self::requestArray($req, 'getGet');
        if ($get !== null) {
            $out['get'] = array_slice(array_keys($get), 0, $maxKeys);
        }

        $post = self::requestArray($req, 'getPost');
        if ($post !== null) {
            $out['post'] = array_slice(array_keys($post), 0, $maxKeys);
        }

        $files = self::requestArray($req, 'getFiles');
        if ($files !== null) {
            $out['files'] = array_slice(array_keys($files), 0, $maxKeys);
        }

        return $out;
    }

    private static function headers(RequestInterface $req, object $config): array
    {
        if (($config->snapshotIncludeHeaders ?? true) !== true) {
            return [];
        }

        $allow = array_map('strtolower', (array) ($config->snapshotHeaderAllowlist ?? []));
        $maxHeaders = max(1, (int) ($config->maxCapturedHeaders ?? 40));
        $maxValueLength = max(80, (int) ($config->maxCapturedHeaderValueLength ?? 800));
        $headers = [];

        foreach ($req->headers() as $name => $headerOrList) {
            $key = strtolower((string) $name);
            if ($allow !== [] && ! in_array($key, $allow, true)) {
                continue;
            }
            if (self::isSensitiveHeader($key)) {
                continue;
            }

            $header = is_array($headerOrList) ? ($headerOrList[0] ?? null) : $headerOrList;
            if (! is_object($header)) {
                continue;
            }

            $value = (string) $header->getValueLine();

            $value = self::sanitizeHeaderValue((string) $name, $value);
            if ($value === '') {
                continue;
            }

            $headers[(string) $name] = mb_substr($value, 0, $maxValueLength);
            if (count($headers) >= $maxHeaders) {
                break;
            }
        }

        return $headers;
    }

    private static function isSensitiveHeader(string $key): bool
    {
        $sensitive = [
            'authorization',
            'cookie',
            'set-cookie',
            'proxy-authorization',
            'x-api-key',
            'x-auth-token',
            'x-csrf-token',
            'x-xsrf-token',
            'php-auth-user',
            'php-auth-pw',
            'x-forwarded-for',
            'x-real-ip',
            'client-ip',
            'x-client-ip',
            'x-cluster-client-ip',
            'true-client-ip',
            'fastly-client-ip',
            'cf-connecting-ip',
            'x-original-forwarded-for',
            'forwarded',
            'from',
            'x-user-id',
            'x-user-email',
        ];

        return in_array($key, $sensitive, true)
            || str_contains($key, 'token')
            || str_contains($key, 'secret')
            || str_contains($key, 'session')
            || str_contains($key, 'credential');
    }

    private static function sanitizeHeaderValue(string $name, string $value): string
    {
        $key = strtolower($name);
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (in_array($key, ['referer', 'referrer'], true)) {
            $parts = parse_url($value);
            if (is_array($parts) && ! empty($parts['host'])) {
                return ((string) ($parts['scheme'] ?? 'https')) . '://' . $parts['host'];
            }
        }

        return preg_replace('/[[:cntrl:]]+/', ' ', $value) ?: '';
    }

    private static function countryFromHeaders(array $headers, ?RequestInterface $req): ?string
    {
        $names = [
            'cf-ipcountry',
            'x-vercel-ip-country',
            'x-country-code',
            'x-appengine-country',
            'cloudfront-viewer-country',
        ];

        foreach ($names as $name) {
            $value = self::headerValue($headers, $name);
            if ($value === null && $req) {
                $value = trim((string) $req->getHeaderLine($name));
            }

            $value = strtoupper(trim((string) $value));
            if (preg_match('/^[A-Z]{2}$/', $value) === 1) {
                return $value;
            }
        }

        return null;
    }

    private static function headerValue(array $headers, string $name): ?string
    {
        foreach ($headers as $key => $value) {
            if (strcasecmp((string) $key, $name) === 0) {
                return (string) $value;
            }
        }

        return null;
    }

    private static function serverInfo(?RequestInterface $req): array
    {
        return [
            'php' => [
                'version' => PHP_VERSION,
                'sapi' => PHP_SAPI,
                'interface' => php_sapi_name(),
                'memory_limit' => self::iniValue('memory_limit'),
                'max_execution_time' => self::iniValue('max_execution_time'),
                'upload_max_filesize' => self::iniValue('upload_max_filesize'),
                'post_max_size' => self::iniValue('post_max_size'),
            ],
            'webserver' => [
                'software' => self::serverValue($req, 'SERVER_SOFTWARE'),
                'gateway_interface' => self::serverValue($req, 'GATEWAY_INTERFACE'),
                'protocol' => self::serverValue($req, 'SERVER_PROTOCOL'),
                'server_name' => self::serverValue($req, 'SERVER_NAME'),
                'server_port' => self::serverValue($req, 'SERVER_PORT'),
            ],
            'os' => [
                'family' => PHP_OS_FAMILY,
                'name' => PHP_OS,
                'machine' => php_uname('m'),
            ],
            'kernel' => [
                'name' => php_uname('s'),
                'release' => php_uname('r'),
                'version' => php_uname('v'),
            ],
        ];
    }

    private static function serverValue(?RequestInterface $req, string $key): ?string
    {
        if (! $req) {
            return null;
        }

        $value = $req->getServer($key);

        return is_scalar($value) ? (string) $value : null;
    }

    private static function iniValue(string $key): ?string
    {
        $value = ini_get($key);

        return $value === false ? null : (string) $value;
    }

    private static function requestAjaxState(RequestInterface $request): ?bool
    {
        if ($request instanceof IncomingRequest) {
            return $request->isAJAX();
        }

        if (! is_callable([$request, 'isAJAX'])) {
            return null;
        }

        return (bool) $request->isAJAX();
    }

    private static function requestArray(RequestInterface $request, string $method): ?array
    {
        if ($request instanceof IncomingRequest) {
            $value = $request->{$method}();

            return is_array($value) ? $value : null;
        }

        if (! is_callable([$request, $method])) {
            return null;
        }

        $value = $request->{$method}();

        return is_array($value) ? $value : null;
    }

    private static function parseUserAgent(string $ua): array
    {
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
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'os' => $os,
            'device' => $device,
            'is_bot' => $device === 'Bot',
        ];
    }
}
