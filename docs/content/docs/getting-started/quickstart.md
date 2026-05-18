---
title: "Quick Start"
description: "Capture your first exception and view it in the dashboard."
weight: 2
---

## Overview

This guide walks you through capturing a test exception with DEX and viewing it as an issue in the dashboard.

## Step 1: Install DEX

If you haven’t installed DEX yet, follow the installation guide:

- [Installation](/docs/getting-started/installation/)

## Step 2: Trigger a test exception

Create a simple route in your `app/Config/Routes.php`:

```php
$routes->get('test-dex', static function () {
    throw new \RuntimeException('Hello from DEX!');
});
```

Visit:

```
http://your-app.test/test-dex
```

You’ll still see the standard CodeIgniter error page. DEX captures the exception in the background.

## Step 3: Open the dashboard

Open:

```
http://your-app.test/dex
```

You should see a new issue for `RuntimeException`.

{{< callout type="info" >}}
DEX groups repeat failures into the same issue using fingerprinting. Each time it happens, you’ll get a new occurrence, and the issue’s counters/timestamps will update.
{{< /callout >}}

## Step 4: Inspect and resolve

Click into the issue to review:

- Stack trace and code context
- Request lifecycle timeline (spans, breadcrumbs, query timing)
- Captured request snapshot (when enabled)

After you fix the bug, mark the issue as **Resolved**.

If the same fingerprint appears again later, DEX reopens it as a regression (`regression` / “Regressed”).

## What’s next

- [How DEX Works](/docs/getting-started/how-dex-works/) — Understand what’s stored and when
- [Configuration Overview](/docs/configuration/overview/) — Configure DEX using `DEX_*` environment variables
- [Security Overview](/docs/security/overview/) — Production hardening guide

