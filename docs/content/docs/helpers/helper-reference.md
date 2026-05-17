---
title: "Helper Reference"
description: "Quick reference for all shipped DEX helper functions."
weight: 55
---

This is a compact reference for the helpers defined in the DEX helper file.

## Config & routing

### `dex_config(): object`

Returns the resolved DEX config object (defaults + `DEX_*` overrides).

### `dex_route_prefix(): string`

Returns the configured route prefix (falls back to `dex`).

## Formatting helpers

### `dex_format_bytes(int|float $bytes): string`

Formats a byte count (e.g. `1048576` → `1.00 MB`).

### `dex_format_ms(int|float $milliseconds): string`

Formats milliseconds into `ms` or seconds.

### `dex_format_datetime(?string $datetime): string`

Formats an ISO/SQL datetime for display using the configured timezone.

### `dex_time_ago(?string $datetime): string`

Returns a human-friendly “time ago” string.

### `dex_age(?string $from, ?string $to = null): string`

Returns the age/duration between two datetimes.

## Debug helpers

### `dex_code_snippet(?string $file, ?int $line, int $radius = 3): ?array`

Reads a small code snippet around a line number.

For safety, it returns `null` when the file:

- Can’t be read, or
- Is outside `ROOTPATH`

## Telemetry helpers

### `dex_breadcrumb(string $category, string $message, array $data = [], string $level = 'info'): void`

Adds a breadcrumb to the current request (if enabled).

### `dex_span_start(string $operation, ?string $description = null, array $tags = []): ?string`

Starts a span and returns its ID.

### `dex_span_finish(?string $id): void`

Finishes a span by ID.

### `dex_span(string $operation, ?string $description, callable $callback, array $tags = [])`

Runs a callback inside a span and returns the callback result.

### `dex_capture_exception(Throwable $exception): void`

Captures a caught exception as an occurrence/issue.

