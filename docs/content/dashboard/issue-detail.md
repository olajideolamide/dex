---
title: "Issue Detail"
description: "Inspect individual issues with full context and event history."
weight: 21
---

## Issue Detail View

Click any issue in the dashboard to open its detail view. This page gives you everything you need to debug and resolve the exception.

## Sections

### Header

The issue header shows:

- **Exception class** and full message
- **Current status** badge (Open, Regressed, Resolved, Ignored)
- **Action buttons** — Resolve, Ignore, or Reopen

### Stack Trace

The full PHP stack trace with syntax-highlighted code context. Each frame shows:

- File path and line number
- The function or method that was called
- Surrounding code lines with the triggering line highlighted

```
app/Models/UserModel.php:47 → findOrFail()
app/Controllers/Api/Users.php:23 → show()
system/CodeIgniter.php:942 → runController()
```

### Request Context

For HTTP-triggered exceptions, DEX captures:

- **Method & URL** — `POST /api/v1/users`
- **Headers** — Content-Type, Authorization (redacted), User-Agent
- **Body** — Request payload (JSON or form data)
- **Query parameters** — URL query string values

{{< callout type="info" >}}
Sensitive headers like `Authorization`, `Cookie`, and `X-API-Key` are automatically redacted. Configure additional headers to redact in your config.
{{< /callout >}}

### Environment

Server and runtime details captured with each event:

- PHP version
- CodeIgniter version
- Server OS and hostname
- Memory usage at time of exception
- Current environment (production, staging, etc.)

### Event Timeline

A chronological list of every occurrence of this issue, with timestamps and request URLs. Click any event to inspect its specific context.

## Actions

### Resolving Issues

Click **Resolve** to mark an issue as fixed. If the same exception occurs again after resolution, DEX automatically marks it as **Regressed**.

### Ignoring Issues

Click **Ignore** to suppress an issue. Ignored issues won't appear in the default dashboard view, and they won't trigger notifications. Events are still captured in case you change your mind.

### Deleting Events

You can delete individual events or bulk-delete all events for an issue. This is useful for cleaning up test data or noise.

{{< callout type="warning" >}}
Deleting events is permanent and cannot be undone. The issue itself remains, but its event history is lost.
{{< /callout >}}
