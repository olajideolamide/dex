---
title: "Performance & Limits"
description: "How DEX keeps overhead low, and which knobs to turn when you need it even lighter."
weight: 15
---

DEX is meant to be safe to run in production without turning your database into a request log.

## DEX does not store every request

DEX keeps an in-memory context for a request, but only stores request rows when the request had an error/exception (or a `5xx` response).

This is one of the biggest levers for keeping storage and overhead low.

## Rate limiting (per issue fingerprint)

If an exception starts firing repeatedly (for example, inside a tight loop), DEX rate-limits occurrence writes per fingerprint using:

- `DEX_MAX_OCCURRENCES_PER_MINUTE` (default: `30`)

Set it to `0` to disable the rate limiter entirely.

## Skip internal and noisy traffic

You can keep DEX out of specific endpoints and health checks:

- `DEX_IGNORE_SELF_ROUTES=true` ignores the DEX dashboard routes under the route prefix
- `DEX_IGNORE_PATH_PREFIXES=["/health","/metrics"]` ignores matching request paths

DEX also blocks common bot user-agent tokens using `DEX_BOT_USER_AGENT_BLOCKLIST`.

## Lifecycle limits

The request lifecycle timeline is capped so it can’t grow without bound:

- `DEX_MAX_LIFECYCLE_ITEMS` caps the number of timeline items
- `DEX_MAX_LIFECYCLE_BYTES` caps the stored JSON size

If you want to drastically reduce stored request detail, consider:

- `DEX_CAPTURE_REQUEST_SNAPSHOTS=false` (stores lifecycle only)
- `DEX_SNAPSHOT_PROFILE=minimal`
- Lower `DEX_MAX_SNAPSHOT_BYTES`

## Next steps

- [Configuration Reference](/docs/configuration/config-reference/) — All config options
- [Data Scrubbing](/docs/advanced/data-scrubbing/) — Redaction rules and privacy tips

