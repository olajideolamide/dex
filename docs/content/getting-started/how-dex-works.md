---
title: "How DEX Works"
description: "A quick mental model for what DEX captures, when it stores data, and where it ends up."
weight: 3
---

DEX is designed to answer one question quickly: **what happened inside this request?**

It does that by building a request context in-memory while CodeIgniter runs, and then persisting a compact snapshot only when something goes wrong.

## What gets captured

During a request, DEX can collect:

- **Exceptions** (handled or unhandled)
- **Breadcrumbs** (small “something happened” markers)
- **Spans** (timed blocks of work)
- **DB queries** (timing + a trimmed SQL string)
- **Lifecycle checkpoints** (CI events like `pre_system`, `post_controller`, etc.)

When an exception is captured, DEX stores:

- An **issue** (the grouped problem)
- An **occurrence** (this one event, including payload and context)
- A **request record** (request lifecycle + optional request snapshot)

## When DEX stores data

DEX intentionally does **not** store every request.

Right now, request rows are stored only when the request had an error/exception (or a `5xx` response). This keeps storage and overhead predictable.

{{< callout type="tip" >}}
You can still add breadcrumbs and spans on every request. They only get persisted when the request ends up being stored.
{{< /callout >}}

## How issues are grouped

Every captured exception is assigned a **fingerprint**. Fingerprints are designed to be stable for repeat failures, while ignoring noise (like changing numbers in the message).

That fingerprint is used as the unique key for the issue, so repeated exceptions:

- Increment the issue’s “times seen”
- Create new occurrences
- Update “last seen” timestamps
- Reopen resolved issues as **regressions**

## Where the data lives

DEX stores data in your application database using three tables:

- `dex_issues` — grouped problems (status, first seen, last seen, times seen)
- `dex_occurrences` — individual events + context payload
- `dex_requests` — stored request rows (snapshot + lifecycle timeline)

## Request IDs

DEX assigns a request ID to each request context:

- If your request already has an ID header (default: `X-Request-Id`), DEX reuses it.
- Otherwise, DEX generates a new one.

DEX then echoes the ID back on the response header so you can correlate browser/network traces with what you see in the dashboard.

## Next steps

- [Configuration Overview]({{< relref "configuration/overview.md" >}}) — Learn how to configure DEX using `DEX_*` environment variables
- [Securing DEX]({{< relref "security/overview.md" >}}) — Recommended production hardening (allowlists, stealth deny, reverse proxy)
