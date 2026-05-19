---
title: "Basic Configuration"
description: "Common DEX settings for routing, capture, and noise control."
weight: 10
---

This page covers the most common configuration tweaks you’ll make after installing DEX.

If you want the full list of options, see:

- [Configuration Reference](/configuration/config-reference/)

## Enable / disable DEX

To disable DEX completely:

```bash
DEX_ENABLED=false
```

## Change the dashboard route prefix

By default, DEX routes live under `/dex`.

To move the UI under a different prefix:

```bash
DEX_ROUTE_PREFIX=ops/dex
```

Your dashboard becomes:

```
/ops/dex/issues
```

## Request IDs

DEX reuses an incoming request ID header (by default `X-Request-Id`) or generates one if missing. It then writes the request ID back to the response header.

If your infrastructure uses a different header, change it:

```bash
DEX_REQUEST_ID_HEADER=X-Correlation-Id
```

## Unhandled exceptions and fatal errors

DEX can capture:

- Unhandled exceptions (global exception handler)
- Fatal shutdown errors (`E_ERROR`, `E_PARSE`, etc.)

You can disable either behavior:

```bash
DEX_CAPTURE_UNHANDLED_EXCEPTIONS=false
DEX_CAPTURE_SHUTDOWN_FATALS=false
```

## Rate limiting noisy issues

If the same exception fires rapidly, DEX rate limits writes per fingerprint:

```bash
DEX_MAX_OCCURRENCES_PER_MINUTE=30
```

Set `0` to disable rate limiting:

```bash
DEX_MAX_OCCURRENCES_PER_MINUTE=0
```

## Ignoring routes and traffic you don’t care about

To keep DEX out of certain endpoints:

```bash
DEX_IGNORE_PATH_PREFIXES=[\"/health\",\"/metrics\"]
```

To prevent DEX from tracking its own UI routes:

```bash
DEX_IGNORE_SELF_ROUTES=true
```

## Next steps

- [Environments](/configuration/environments/) — Safe production enablement and per-environment settings
- [Performance & Limits](/configuration/performance-and-limits/) — Reduce overhead and storage
- [Purging & Retention](/configuration/notifications/) — Keep the DEX tables from growing forever
