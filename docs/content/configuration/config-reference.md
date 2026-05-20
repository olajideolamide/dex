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

- `enabled`
  - Env: `DEX_ENABLED`
  - Type: `bool`
  - Default: `true`
  - Notes: Turns DEX on/off globally.
- `routePrefix`
  - Env: `DEX_ROUTE_PREFIX`
  - Type: `string`
  - Default: `dex`
  - Notes: URL prefix used by the dashboard routes.
- `requestIdHeader`
  - Env: `DEX_REQUEST_ID_HEADER`
  - Type: `string`
  - Default: `X-Request-Id`
  - Notes: Header to read/write request IDs.

## Production access controls

- `allowInProduction`
  - Env: `DEX_ALLOW_IN_PRODUCTION`
  - Type: `bool`
  - Default: `false`
  - Notes: Blocks the UI in `production` unless enabled.
- `uiEnabled`
  - Env: `DEX_UI_ENABLED`
  - Type: `bool`
  - Default: `true`
  - Notes: Master switch for the UI routes.
- `uiAllowlist`
  - Env: `DEX_UI_ALLOWLIST`
  - Type: `string`
  - Default: `127.0.0.1,::1`
  - Notes: Comma-separated list of IPs / CIDR ranges allowed to access the UI.
- `allowedIPs`
  - Env: `DEX_ALLOWED_IPS`
  - Type: `array`
  - Default: `[]`
  - Notes: Optional exact-match IP allowlist. If non-empty, the client IP must be in this list **and** pass `uiAllowlist`. **Deprecated:** use `uiAllowlist` instead. This will be removed in a future major release.
- `uiStealthDeny`
  - Env: `DEX_UI_STEALTH_DENY`
  - Type: `bool`
  - Default: `true`
  - Notes: When denied, return `404` (stealth) instead of `403`.

## Error capture

- `captureUnhandledExceptions`
  - Env: `DEX_CAPTURE_UNHANDLED_EXCEPTIONS`
  - Type: `bool`
  - Default: `true`
  - Notes: Registers a global exception handler to capture unhandled exceptions.
- `captureShutdownFatals`
  - Env: `DEX_CAPTURE_SHUTDOWN_FATALS`
  - Type: `bool`
  - Default: `true`
  - Notes: Captures fatal shutdown errors (`E_ERROR`, `E_PARSE`, etc.).

## Request snapshots

Request snapshots are stored as JSON on the request row when DEX stores the request.

- `captureRequestSnapshots`
  - Env: `DEX_CAPTURE_REQUEST_SNAPSHOTS`
  - Type: `bool`
  - Default: `true`
  - Notes: Stores a request snapshot JSON alongside lifecycle JSON.
- `snapshotProfile`
  - Env: `DEX_SNAPSHOT_PROFILE`
  - Type: `string`
  - Default: `full`
  - Notes: `full` or `minimal`. Minimal stores a smaller subset.
- `maxSnapshotBytes`
  - Env: `DEX_MAX_SNAPSHOT_BYTES`
  - Type: `int`
  - Default: `48000`
  - Notes: Hard cap for stored snapshot JSON size.
- `snapshotIncludeInputKeys`
  - Env: `DEX_SNAPSHOT_INCLUDE_INPUT_KEYS`
  - Type: `bool`
  - Default: `true`
  - Notes: Stores input key names (GET/POST/FILES), not values.
- `snapshotMaxKeys`
  - Env: `DEX_SNAPSHOT_MAX_KEYS`
  - Type: `int`
  - Default: `200`
  - Notes: Max keys per input group (GET/POST/FILES).
- `snapshotIncludeHeaders`
  - Env: `DEX_SNAPSHOT_INCLUDE_HEADERS`
  - Type: `bool`
  - Default: `true`
  - Notes: Stores request headers in snapshot (sensitive headers are excluded).
- `snapshotHeaderAllowlist`
  - Env: `DEX_SNAPSHOT_HEADER_ALLOWLIST`
  - Type: `array`
  - Default: `[]`
  - Notes: If provided, only these header names are included (case-insensitive).

## Noise controls & internal traffic

- `maxOccurrencesPerMinute`
  - Env: `DEX_MAX_OCCURRENCES_PER_MINUTE`
  - Type: `int`
  - Default: `30`
  - Notes: Rate limit per fingerprint per minute. Set `0` to disable.
- `ignoreSelfRoutes`
  - Env: `DEX_IGNORE_SELF_ROUTES`
  - Type: `bool`
  - Default: `true`
  - Notes: Ignores DEX UI routes so DEX doesn’t record itself.
- `ignorePathPrefixes`
  - Env: `DEX_IGNORE_PATH_PREFIXES`
  - Type: `array`
  - Default: `[]`
  - Notes: Path prefixes to ignore (e.g. `["/health", "/metrics"]`).
- `botUserAgentBlocklist`
  - Env: `DEX_BOT_USER_AGENT_BLOCKLIST`
  - Type: `array`
  - Default: _(built-in list)_
  - Notes: If the request UA contains one of these tokens, DEX won’t run.
- `ignoreNamespaces`
  - Env: `DEX_IGNORE_NAMESPACES`
  - Type: `array`
  - Default: `["Dex\\\\"]`
  - Notes: Exceptions originating from these namespaces won’t be captured.

## Breadcrumbs, spans, and lifecycle timeline

- `captureLifecycle`
  - Env: `DEX_CAPTURE_LIFECYCLE`
  - Type: `bool`
  - Default: `true`
  - Notes: Enables the lifecycle timeline collection.
- `maxLifecycleItems`
  - Env: `DEX_MAX_LIFECYCLE_ITEMS`
  - Type: `int`
  - Default: `220`
  - Notes: Caps the number of lifecycle items stored per request.
- `maxLifecycleBytes`
  - Env: `DEX_MAX_LIFECYCLE_BYTES`
  - Type: `int`
  - Default: `128000`
  - Notes: Hard cap for stored lifecycle JSON size.
- `maxLifecycleItemDataBytes`
  - Env: `DEX_MAX_LIFECYCLE_ITEM_DATA_BYTES`
  - Type: `int`
  - Default: `6000`
  - Notes: Caps the size of per-item metadata before storing.
- `slowRequestMs`
  - Env: `DEX_SLOW_REQUEST_MS`
  - Type: `int`
  - Default: `1000`
  - Notes: Marks stored requests as “slow” when duration meets/exceeds this threshold.
- `slowQueryMs`
  - Env: `DEX_SLOW_QUERY_MS`
  - Type: `int`
  - Default: `100`
  - Notes: Marks DB queries as “slow” in the lifecycle timeline.
- `maxSqlLength`
  - Env: `DEX_MAX_SQL_LENGTH`
  - Type: `int`
  - Default: `4000`
  - Notes: Trims captured SQL strings to this many characters.
- `captureBreadcrumbs`
  - Env: `DEX_CAPTURE_BREADCRUMBS`
  - Type: `bool`
  - Default: `true`
  - Notes: Enables breadcrumbs.
- `captureSpans`
  - Env: `DEX_CAPTURE_SPANS`
  - Type: `bool`
  - Default: `true`
  - Notes: Enables spans.
- `captureCiLifecycle`
  - Env: `DEX_CAPTURE_CI_LIFECYCLE`
  - Type: `bool`
  - Default: `true`
  - Notes: Enables CI lifecycle checkpoints/spans (route matched, controller span, etc.).
- `captureCiLifecycleBreadcrumbs`
  - Env: `DEX_CAPTURE_CI_LIFECYCLE_BREADCRUMBS`
  - Type: `bool`
  - Default: `true`
  - Notes: Adds a breadcrumb for certain CI events.

{{< callout type="info" >}}
If you need hard caps, rely on `maxLifecycleItems` and the JSON byte limits.
{{< /callout >}}

## Redaction / scrubbing

- `scrubFields`
  - Env: `DEX_SCRUB_FIELDS`
  - Type: `array`
  - Default: _(password/token list)_
  - Notes: Field names that will be redacted from stored payloads and snapshots.

## Occurrence HTTP context

- `captureHttpOnError`
  - Env: `DEX_CAPTURE_HTTP_ON_ERROR`
  - Type: `bool`
  - Default: `true`
  - Notes: Captures compact HTTP context when an exception is stored.
- `captureRequestHeadersOnError`
  - Env: `DEX_CAPTURE_REQUEST_HEADERS_ON_ERROR`
  - Type: `bool`
  - Default: `false`
  - Notes: When enabled, includes request headers in occurrence HTTP context.
- `maxCapturedHeaders`
  - Env: `DEX_MAX_CAPTURED_HEADERS`
  - Type: `int`
  - Default: `40`
  - Notes: Max headers stored when header capture is enabled.
- `maxCapturedHeaderValueLength`
  - Env: `DEX_MAX_CAPTURED_HEADER_VALUE_LENGTH`
  - Type: `int`
  - Default: `800`
  - Notes: Trims header values to this length.

## Data purging (retention)

DEX includes a purge job you can run via CLI: `php spark dex:purge`.

- `purgeEnabled`
  - Env: `DEX_PURGE_ENABLED`
  - Type: `bool`
  - Default: `true`
  - Notes: Enables the purge job.
- `purgeBatchSize`
  - Env: `DEX_PURGE_BATCH_SIZE`
  - Type: `int`
  - Default: `500`
  - Notes: Delete batch size (per loop).
- `purgeMaxRuntimeSeconds`
  - Env: `DEX_PURGE_MAX_RUNTIME_SECONDS`
  - Type: `int`
  - Default: `20`
  - Notes: Safety cap for one purge run.
- `purgeRetentionDaysRequests`
  - Env: `DEX_PURGE_RETENTION_DAYS_REQUESTS`
  - Type: `int`
  - Default: `14`
  - Notes: TTL for request rows.
- `purgeRetentionDaysOccurrences`
  - Env: `DEX_PURGE_RETENTION_DAYS_OCCURRENCES`
  - Type: `int`
  - Default: `30`
  - Notes: TTL for occurrences.
- `purgeRetentionDaysIssues`
  - Env: `DEX_PURGE_RETENTION_DAYS_ISSUES`
  - Type: `int`
  - Default: `90`
  - Notes: TTL for issues.
- `purgeMaxRowsRequests`
  - Env: `DEX_PURGE_MAX_ROWS_REQUESTS`
  - Type: `int`
  - Default: `100000`
  - Notes: Row cap safety net (keeps newest rows).
- `purgeMaxRowsOccurrences`
  - Env: `DEX_PURGE_MAX_ROWS_OCCURRENCES`
  - Type: `int`
  - Default: `200000`
  - Notes: Row cap safety net (keeps newest rows).
- `purgeMaxRowsIssues`
  - Env: `DEX_PURGE_MAX_ROWS_ISSUES`
  - Type: `int`
  - Default: `50000`
  - Notes: Row cap safety net (keeps newest rows).
- `purgeUseDbLock`
  - Env: `DEX_PURGE_USE_DB_LOCK`
  - Type: `bool`
  - Default: `true`
  - Notes: Use DB-level lock to avoid concurrent purges.
- `purgeDbLockName`
  - Env: `DEX_PURGE_DB_LOCK_NAME`
  - Type: `string`
  - Default: `dex_purge`
  - Notes: Lock name used by the purge job.
