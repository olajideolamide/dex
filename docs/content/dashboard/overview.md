---
title: "Dashboard Overview"
description: "Navigate the DEX dashboard to triage and manage issues."
weight: 20
---

## Accessing the dashboard

The DEX dashboard is available at:

```
http://your-app.test/dex
```

Replace `/dex` with your configured `routePrefix` if you’ve changed it.

## Dashboard sections

### Issues list

The main view shows captured issues with the following columns:

- **Issue** — Exception class name, message preview, and where it occurred
- **Status** — Open, Resolved, Regressed, or Ignored
- **Events** — Total number of times this issue has occurred
- **24h** — Occurrences in the last 24 hours
- **Trend** — Sparkline showing recent volume
- **Age** — How long ago the issue was first seen

### Filtering issues

Use the status tabs to filter issues:

- **All** — Every issue regardless of status
- **Open** — New, unresolved issues
- **Regressed** — Issues that were resolved but reappeared
- **Resolved** — Issues marked as fixed
- **Ignored** — Issues you’ve chosen to suppress

### Event volume

The top of the dashboard shows a volume chart for recent occurrences. This is useful for spotting spikes after a deployment.

## Summary cards

At the top of the dashboard, the summary cards give you a quick pulse:

| Card | Description |
|------|-------------|
| **Open** | Number of unresolved issues |
| **Regressed** | Issues that came back after being resolved |
| **Events (24h)** | Total occurrences in the last 24 hours |
| **Trend** | Change vs the previous 24 hours |

