---
title: "Helper Reference"
description: "Quick reference for all shipped DEX helper functions."
weight: 54
---

DEX ships helper functions in `src/Helpers/dex_helper.php` (loaded via `helper('dex')`).

This page is a quick reference for signatures and intent. For usage patterns, see:

- [Helpers Overview]({{< relref "helpers/overview.md" >}})
- [Breadcrumbs]({{< relref "helpers/breadcrumbs.md" >}})
- [Spans]({{< relref "helpers/spans.md" >}})
- [Capturing Exceptions]({{< relref "helpers/capturing-exceptions.md" >}})

## Configuration & routing

- `dex_config(): object` — Return the resolved DEX config.
- `dex_route_prefix(): string` — Return the route prefix (defaults to `dex`).

## Formatting helpers

- `dex_format_bytes(int|float $bytes): string` — Format bytes as human-readable units.
- `dex_format_ms(int|float $milliseconds): string` — Format durations in ms (or seconds).
- `dex_format_datetime(?string $datetime): string` — Format datetimes for display.
- `dex_time_ago(?string $datetime): string` — Relative time (e.g. “5 minutes ago”).
- `dex_age(?string $from, ?string $to = null): string` — Age between two datetimes.

## Telemetry helpers

- `dex_breadcrumb(string $category, string $message, array $data = [], string $level = 'info'): void` — Add a breadcrumb.
- `dex_span_start(string $operation, ?string $description = null, array $tags = []): ?string` — Start a span and return its ID.
- `dex_span_finish(?string $id): void` — Finish a span.
- `dex_span(string $operation, ?string $description, callable $callback, array $tags = [])` — Run a callback inside a span.
- `dex_capture_exception(Throwable $exception): void` — Capture a caught exception.

## UI helpers

- `dex_code_snippet(?string $file, ?int $line, int $radius = 3): ?array` — Read a small snippet around a line (safe-guarded).
- `dex_kv_table(array $rows): string` — Render a small key/value table for the dashboard.
- `dex_code_block(string $text, string $copy = ''): string` — Render a code block (optionally copyable) for the dashboard.

