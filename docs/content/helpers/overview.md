---
title: "Helpers Overview"
description: "Use helper functions to add breadcrumbs, spans, and manual exception captures."
weight: 50
---

DEX ships with a small set of helper functions (loaded via `helper('dex')`) that let you add extra context to requests with minimal effort.

The helpers are intentionally defensive: if DEX is disabled or unavailable, they **silently do nothing** so they never break your app.

## What helpers are for

Use helpers when you want to:

- Add breadcrumbs while handling something “interesting”
- Time a block of work with spans
- Report a caught exception (so it still shows up as an issue)

## Next steps

- [Capturing Exceptions]({{< relref "helpers/capturing-exceptions.md" >}}) — `dex_capture_exception()`
- [Breadcrumbs]({{< relref "helpers/breadcrumbs.md" >}}) — `dex_breadcrumb()`
- [Spans]({{< relref "helpers/spans.md" >}}) — `dex_span_start()`, `dex_span_finish()`, `dex_span()`
- [Helper Reference]({{< relref "helpers/helper-reference.md" >}}) — Quick signatures and behavior notes
