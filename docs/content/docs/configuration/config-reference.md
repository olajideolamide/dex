---
title: "Configuration Reference"
description: "A complete reference for every DEX configuration option and its DEX_* environment variable."
weight: 13
---

This page documents the full configuration surface in `Dex\\Config\\Dex` and the matching `DEX_*` environment variables.

{{< callout type="tip" >}}
All options are optional — if you don’t set an environment variable, DEX uses its default.
{{< /callout >}}

## Core switches & routing

| Option | Env | Type | Default | Notes |
|---|---|---:|---|---|
| `enabled` | `DEX_ENABLED` | bool | `true` | Turns DEX on/off globally. |
| `routePrefix` | `DEX_ROUTE_PREFIX` | string | `dex` | URL prefix used by the dashboard routes. |
| `requestIdHeader` | `DEX_REQUEST_ID_HEADER` | string | `X-Request-Id` | Header to read/write request IDs. |

## Production access controls

| Option | Env | Type | Default | Notes |
|---|---|---:|---|---|
| `allowInProduction` | `DEX_ALLOW_IN_PRODUCTION` | bool | `false` | Blocks the UI in `production` unless enabled. |
| `uiEnabled` | `DEX_UI_ENABLED` | bool | `true` | Master switch for the UI routes. |
| `uiAllowlist` | `DEX_UI_ALLOWLIST` | string | `127.0.0.1,::1` | Comma-separated list of IPs / CIDR ranges allowed to access the UI. |
| `allowedIPs` | `DEX_ALLOWED_IPS` | array | `[]` | Optional exact-match IP allowlist. If non-empty, the client IP must be in this list **and** pass `uiAllowlist`. |
| `uiStealthDeny` | `DEX_UI_STEALTH_DENY` | bool | `true` | When denied, return `404` (stealth) instead of `403`. |

## Error capture

| Option | Env | Type | Default | Notes |
|---|---|---:|---|---|
| `captureUnhandledExceptions` | `DEX_CAPTURE_UNHANDLED_EXCEPTIONS` | bool | `true` | Registers a global exception handler to capture unhandled exceptions. |
| `captureShutdownFatals` | `DEX_CAPTURE_SHUTDOWN_FATALS` | bool | `true` | Captures fatal shutdown errors (`E_ERROR`, `E_PARSE`, etc.). |

## Request snapshots

Request snapshots are stored as JSON on the request row when DEX stores the request.

| Option | Env | Type | Default | Notes |
|---|---|---:|---|---|
| `captureRequestSnapshots` | `DEX_CAPTURE_REQUEST_SNAPSHOTS` | bool | `true` | Stores a request snapshot JSON alongside lifecycle JSON. |
| `snapshotProfile` | `DEX_SNAPSHOT_PROFILE` | string | `full` | `full` or `minimal`. Minimal stores a smaller subset. |
| `maxSnapshotBytes` | `DEX_MAX_SNAPSHOT_BYTES` | int | `48000` | Hard cap for stored snapshot JSON size. |
| `snapshotIncludeInputKeys` | `DEX_SNAPSHOT_INCLUDE_INPUT_KEYS` | bool | `true` | Stores input key names (GET/POST/FILES), not values. |
| `snapshotMaxKeys` | `DEX_SNAPSHOT_MAX_KEYS` | int | `200` | Max keys per input group (GET/POST/FILES). |
| `snapshotIncludeHeaders` | `DEX_SNAPSHOT_INCLUDE_HEADERS` | bool | `true` | Stores request headers in snapshot (sensitive headers are excluded). |
| `snapshotHeaderAllowlist` | `DEX_SNAPSHOT_HEADER_ALLOWLIST` | array | `[]` | If provided, only these header names are included (case-insensitive). |

## Noise controls & internal traffic

| Option | Env | Type | Default | Notes |
|---|---|---:|---|---|
| `maxOccurrencesPerMinute` | `DEX_MAX_OCCURRENCES_PER_MINUTE` | int | `30` | Rate limit per fingerprint per minute. Set `0` to disable. |
| `ignoreSelfRoutes` | `DEX_IGNORE_SELF_ROUTES` | bool | `true` | Ignores DEX UI routes so DEX doesn’t record itself. |
| `ignorePathPrefixes` | `DEX_IGNORE_PATH_PREFIXES` | array | `[]` | Path prefixes to ignore (e.g. `["/health", "/metrics"]`). |
| `botUserAgentBlocklist` | `DEX_BOT_USER_AGENT_BLOCKLIST` | array | _(built-in list)_ | If the request UA contains one of these tokens, DEX won’t run. |
| `ignoreNamespaces` | `DEX_IGNORE_NAMESPACES` | array | `["Dex\\\\"]` | Exceptions originating from these namespaces won’t be captured. |

## Breadcrumbs, spans, and lifecycle timeline

| Option | Env | Type | Default | Notes |
|---|---|---:|---|---|
| `captureLifecycle` | `DEX_CAPTURE_LIFECYCLE` | bool | `true` | Enables the lifecycle timeline collection. |
| `maxLifecycleItems` | `DEX_MAX_LIFECYCLE_ITEMS` | int | `220` | Caps the number of lifecycle items stored per request. |
| `maxLifecycleBytes` | `DEX_MAX_LIFECYCLE_BYTES` | int | `128000` | Hard cap for stored lifecycle JSON size. |
| `maxLifecycleItemDataBytes` | `DEX_MAX_LIFECYCLE_ITEM_DATA_BYTES` | int | `6000` | Caps the size of per-item metadata before storing. |
| `slowRequestMs` | `DEX_SLOW_REQUEST_MS` | int | `1000` | Marks stored requests as “slow” when duration meets/exceeds this threshold. |
| `slowQueryMs` | `DEX_SLOW_QUERY_MS` | int | `100` | Marks DB queries as “slow” in the lifecycle timeline. |
| `maxSqlLength` | `DEX_MAX_SQL_LENGTH` | int | `4000` | Trims captured SQL strings to this many characters. |
| `captureBreadcrumbs` | `DEX_CAPTURE_BREADCRUMBS` | bool | `true` | Enables breadcrumbs. |
| `captureSpans` | `DEX_CAPTURE_SPANS` | bool | `true` | Enables spans. |
| `captureCiLifecycle` | `DEX_CAPTURE_CI_LIFECYCLE` | bool | `true` | Enables CI lifecycle checkpoints/spans (route matched, controller span, etc.). |
| `captureCiLifecycleBreadcrumbs` | `DEX_CAPTURE_CI_LIFECYCLE_BREADCRUMBS` | bool | `true` | Adds a breadcrumb for certain CI events. |

{{< callout type="info" >}}
`maxBreadcrumbs`, `maxSpans`, `maxLifecycleMarkers`, `duplicateQueryThreshold`, `nPlusOneQueryThreshold`, and `breadcrumbDbQueries` exist as config knobs, but are not currently enforced everywhere. If you need hard caps, rely on `maxLifecycleItems` and the JSON byte limits for now.
{{< /callout >}}

## Redaction / scrubbing

| Option | Env | Type | Default | Notes |
|---|---|---:|---|---|
| `scrubFields` | `DEX_SCRUB_FIELDS` | array | _(password/token list)_ | Field names that will be redacted from stored payloads and snapshots. |

## Occurrence HTTP context

| Option | Env | Type | Default | Notes |
|---|---|---:|---|---|
| `captureHttpOnError` | `DEX_CAPTURE_HTTP_ON_ERROR` | bool | `true` | Captures compact HTTP context when an exception is stored. |
| `captureRequestHeadersOnError` | `DEX_CAPTURE_REQUEST_HEADERS_ON_ERROR` | bool | `false` | When enabled, includes request headers in occurrence HTTP context. |
| `maxCapturedHeaders` | `DEX_MAX_CAPTURED_HEADERS` | int | `40` | Max headers stored when header capture is enabled. |
| `maxCapturedHeaderValueLength` | `DEX_MAX_CAPTURED_HEADER_VALUE_LENGTH` | int | `800` | Trims header values to this length. |

## Data purging (retention)

DEX includes a purge job you can run via CLI: `php spark dex:purge`.

| Option | Env | Type | Default | Notes |
|---|---|---:|---|---|
| `purgeEnabled` | `DEX_PURGE_ENABLED` | bool | `true` | Enables the purge job. |
| `purgeBatchSize` | `DEX_PURGE_BATCH_SIZE` | int | `500` | Delete batch size (per loop). |
| `purgeMaxRuntimeSeconds` | `DEX_PURGE_MAX_RUNTIME_SECONDS` | int | `20` | Safety cap for one purge run. |
| `purgeRetentionDaysRequests` | `DEX_PURGE_RETENTION_DAYS_REQUESTS` | int | `14` | TTL for request rows. |
| `purgeRetentionDaysOccurrences` | `DEX_PURGE_RETENTION_DAYS_OCCURRENCES` | int | `30` | TTL for occurrences. |
| `purgeRetentionDaysIssues` | `DEX_PURGE_RETENTION_DAYS_ISSUES` | int | `90` | TTL for issues. |
| `purgeMaxRowsRequests` | `DEX_PURGE_MAX_ROWS_REQUESTS` | int | `100000` | Row cap safety net (keeps newest rows). |
| `purgeMaxRowsOccurrences` | `DEX_PURGE_MAX_ROWS_OCCURRENCES` | int | `200000` | Row cap safety net (keeps newest rows). |
| `purgeMaxRowsIssues` | `DEX_PURGE_MAX_ROWS_ISSUES` | int | `50000` | Row cap safety net (keeps newest rows). |
| `purgeUseDbLock` | `DEX_PURGE_USE_DB_LOCK` | bool | `true` | Use DB-level lock to avoid concurrent purges. |
| `purgeDbLockName` | `DEX_PURGE_DB_LOCK_NAME` | string | `dex_purge` | Lock name used by the purge job. |

