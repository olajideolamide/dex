---
title: "PII & Data Handling"
description: "Keep DEX useful while minimizing the risk of storing sensitive information."
weight: 74
---

DEX is most valuable when it stores enough context to debug issues but that context can include sensitive inputs if you’re not careful.

This page covers a practical approach: store what you need to debug, scrub what you shouldn’t keep.

## What DEX stores

Depending on your config, DEX may store:

- Exception payloads (message, file, line, stack trace)
- HTTP context (method, URL/path, IP, user agent)
- Request snapshots (headers and **input key names**, not values)
- Lifecycle timelines (breadcrumbs, spans, query timings)

## Scrubbing strategy

DEX supports field scrubbing via `DEX_SCRUB_FIELDS`. These field names are redacted from stored snapshots and payloads.

Example:

```bash
DEX_SCRUB_FIELDS=[\"password\",\"token\",\"authorization\",\"cookie\"]
```

{{< callout type="tip" >}}
Prefer to add scrubbing rules proactively before you enable DEX on production traffic.
{{< /callout >}}

## Avoid storing headers unless you need them

Two places can capture headers:

- Request snapshots (`DEX_SNAPSHOT_INCLUDE_HEADERS`)
- Occurrence HTTP context (`DEX_CAPTURE_REQUEST_HEADERS_ON_ERROR`)

If you’re not actively using headers for debugging, leave occurrence header capture off (default).

## Don’t store values you don’t need

Request snapshots include input **keys** (GET/POST/FILES), not values. That’s intentional:

- You can still see what kind of payload the request had
- You avoid storing raw credentials and personal data by default

If you need deeper request data for a short period, consider:

- Reducing retention days
- Restricting UI access further
- Tightening scrubbing rules

