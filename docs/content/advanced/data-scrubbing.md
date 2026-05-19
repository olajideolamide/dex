---
title: "Data Scrubbing"
description: "Redact sensitive fields from stored snapshots and payloads."
weight: 42
---

DEX stores error context and request snapshots. Some of that data can be sensitive.

Scrubbing is your safety net: it redacts specific field names from stored JSON before it is persisted.

## What gets scrubbed

DEX applies scrubbing to:

- Stored request snapshots (`dex_requests.snapshot_json`)
- Stored request lifecycle (`dex_requests.lifecycle_json`)
- Stored occurrence payloads (`dex_occurrences.context`)

## Configure scrub fields

Scrubbing is controlled by:

- `DEX_SCRUB_FIELDS` (array)

Example:

```bash
DEX_SCRUB_FIELDS=[\"password\",\"token\",\"authorization\",\"cookie\"]
```

You can also provide a comma-separated list:

```bash
DEX_SCRUB_FIELDS=password,token,authorization,cookie
```

{{< callout type="warning" >}}
Scrubbing is based on field names. If your app uses custom names (for example `pin`, `otp`, `secret_key`), add them explicitly.
{{< /callout >}}

## Header capture considerations

DEX can capture headers in two places:

- Request snapshots (controlled by `DEX_SNAPSHOT_INCLUDE_HEADERS`)
- Occurrence HTTP context (controlled by `DEX_CAPTURE_REQUEST_HEADERS_ON_ERROR`)

Even when header capture is enabled, DEX excludes a list of sensitive headers (like `Authorization` and `Cookie`).

If you don’t need headers for debugging, keep occurrence header capture off (default).

## Next steps

- [PII & Data Handling]({{< relref "security/pii-and-data-handling.md" >}}) — Production-safe defaults and retention tips
- [Configuration Reference]({{< relref "configuration/config-reference.md" >}}) — All config options
