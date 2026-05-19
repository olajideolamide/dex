---
title: "Introduction"
description: "Learn what DEX is, what it captures, and how it fits into your CodeIgniter 4 application."
weight: -1
---

DEX is an open-source issue tracker built for **CodeIgniter 4** applications.

It helps you answer questions like:

- What broke?
- Where did it break?
- How often is it happening?
- Did it start after a deployment?

DEX captures exceptions from your application, groups repeating problems into **issues**, and stores each occurrence as an **event** so you can triage, resolve, and track regressions over time.

{{< callout type="info" >}}
DEX is designed to be CI4-native and self-hosted. You keep your data and run it alongside your app.
{{< /callout >}}

## What DEX Does

- **Captures exceptions** thrown by your application
- **Groups events into issues** using fingerprinting (so duplicates don’t flood your dashboard)
- **Tracks issue status** (open, resolved, regressed, ignored)
- **Provides a dashboard** for browsing, searching, and inspecting issues

## Concepts

DEX uses a few simple terms throughout the dashboard and API:

- **Issue**: A grouped problem (e.g. a repeating exception) identified by a fingerprint
- **Event**: One occurrence of an issue (each time it happens)
- **Fingerprint**: The grouping key used to decide which issue an exception belongs to
- **Regression**: When an issue you marked as resolved starts happening again

## When to Use DEX

DEX is a good fit when you want:

- A lightweight, self-hosted way to track exceptions
- Visibility into repeat failures and regressions
- A simple dashboard your team can use during triage

If you need a full APM with distributed tracing and metrics, DEX can still be useful as your “errors inbox”, but it’s intentionally focused on issue tracking.

## Next Steps

- [Installation](/getting-started/installation/) — Install DEX and run the installer
- [Quick Start](/getting-started/quickstart/) — Trigger a test exception and see it in the dashboard
