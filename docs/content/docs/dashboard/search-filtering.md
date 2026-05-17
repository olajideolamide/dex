---
title: "Search & Filtering"
description: "Find specific issues using search and advanced filters."
weight: 22
---

## Search

The search bar at the top of the issues list lets you search by:

- Exception class name (e.g., `DatabaseException`)
- Exception message text
- Request URL or endpoint
- Environment name

Search is case-insensitive and supports partial matching.

## Filters

### Status Filter

Filter issues by their current status using the tab bar:

```
All | Open | Regressed | Resolved | Ignored
```

### Date Range

Filter issues by when they were first seen or last occurred:

- **Last 24 hours**
- **Last 7 days**
- **Last 30 days**
- **Custom range**

### Sort Options

Sort the issues list by:

| Sort | Description |
|------|-------------|
| **Events** | Total event count (default) |
| **Last seen** | Most recently occurred |
| **First seen** | When the issue was first captured |
| **24h events** | Recent activity |
| **Trend** | Issues getting worse |

{{< callout type="tip" >}}
Sorting by **Trend** helps you find issues that are rapidly increasing in frequency — these often indicate a new deployment problem.
{{< /callout >}}

## Keyboard Shortcuts

The dashboard supports keyboard shortcuts for fast navigation:

| Shortcut | Action |
|----------|--------|
| `/` | Focus search |
| `j` / `k` | Navigate up/down in issue list |
| `Enter` | Open selected issue |
| `r` | Resolve selected issue |
| `i` | Ignore selected issue |
| `Esc` | Close detail view |
