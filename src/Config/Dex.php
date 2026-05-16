<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Dex\Config;

use CodeIgniter\Config\BaseConfig;

class Dex extends BaseConfig
{
    /* -------------------------------------------------------------------------
     | Core switches & routing / environment access
     | ------------------------------------------------------------------------- */
    public bool $enabled = true;
    public string $routePrefix = 'dex';
    public bool $allowInProduction = false;
    public array $allowedIPs = [];
    public string $requestIdHeader = 'X-Request-Id';

    /* -------------------------------------------------------------------------
     | Error capture
     | ------------------------------------------------------------------------- */
    public bool $captureUnhandledExceptions = true;
    public bool $captureShutdownFatals = true;

    /* -------------------------------------------------------------------------
     | Request snapshots
     | ------------------------------------------------------------------------- */
    public bool $captureRequestSnapshots = true;
    public string $snapshotProfile = 'full';
    public int $maxSnapshotBytes = 48000;
    public bool $snapshotIncludeInputKeys = true;
    public int $snapshotMaxKeys = 200;

    public bool $snapshotIncludeHeaders = true;
    public array $snapshotHeaderAllowlist = [];

    /* -------------------------------------------------------------------------
     | Safety / noise controls
     | ------------------------------------------------------------------------- */
    public int $maxOccurrencesPerMinute = 30;
    public bool $ignoreSelfRoutes = true;
    public array $ignorePathPrefixes = [];
    public array $botUserAgentBlocklist = [
        'googlebot',
        'bingbot',
        'slurp',
        'duckduckbot',
        'baiduspider',
        'yandexbot',
        'sogou',
        'facebookexternalhit',
        'twitterbot',
        'linkedinbot',
        'applebot',
        'semrushbot',
        'ahrefsbot',
        'mj12bot',
    ];
    public array $ignoreNamespaces = [
        'Dex\\',
    ];

    /* -------------------------------------------------------------------------
     | Storage + tracing
     | ------------------------------------------------------------------------- */
    public string $storage = 'database';

    public bool $captureBreadcrumbs = true;
    public int $maxBreadcrumbs = 50;

    public bool $captureSpans = true;
    public int $maxSpans = 60;

    public bool $captureLifecycle = true;
    public int $maxLifecycleItems = 220;
    public int $maxLifecycleBytes = 128000;
    public int $maxLifecycleItemDataBytes = 6000;
    public int $slowRequestMs = 1000;
    public int $slowQueryMs = 100;
    public int $duplicateQueryThreshold = 3;
    public int $nPlusOneQueryThreshold = 10;

    public bool $captureCiLifecycleBreadcrumbs = true;
    public bool $captureCiLifecycle = true;
    public int $maxLifecycleMarkers = 80;

    public bool $breadcrumbDbQueries = true;
    public int $maxSqlLength = 4000;

    /* -------------------------------------------------------------------------
     | Redaction / scrubbing
     | ------------------------------------------------------------------------- */
    public array $scrubFields = [
        'password',
        'pass',
        'pwd',
        'token',
        'access_token',
        'refresh_token',
        'authorization',
        'cookie',
        'set-cookie',
        'api_key',
        'apikey',
        'secret',
        'client_secret',
    ];

    /* -------------------------------------------------------------------------
     | Occurrence context extras
     | ------------------------------------------------------------------------- */
    public bool $captureHttpOnError = true;
    public bool $captureRequestHeadersOnError = false;
    public int $maxCapturedHeaders = 40;
    public int $maxCapturedHeaderValueLength = 800;

    /* -------------------------------------------------------------------------
     | UI access
     | ------------------------------------------------------------------------- */
    public bool $uiEnabled = true;
    public string $uiAllowlist = '127.0.0.1,::1';
    public bool $uiStealthDeny = true;
    public string $displayTimezone = 'UTC';

    /* -------------------------------------------------------------------------
     | Data purging
     | ------------------------------------------------------------------------- */
    public bool $purgeEnabled = true;
    public int $purgeBatchSize = 500;
    public int $purgeMaxRuntimeSeconds = 20;
    public int $purgeRetentionDaysRequests = 14;
    public int $purgeRetentionDaysOccurrences = 30;
    public int $purgeRetentionDaysIssues = 90;
    public int $purgeMaxRowsRequests = 100_000;
    public int $purgeMaxRowsOccurrences = 200_000;
    public int $purgeMaxRowsIssues = 50_000;
    public bool $purgeUseDbLock = true;
    public string $purgeDbLockName = 'dex_purge';
}
