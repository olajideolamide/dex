---
title: "Installation"
description: "Install DEX via Composer and integrate it with your CodeIgniter 4 application."
weight: 1
---

## Requirements

Before installing DEX, make sure your environment meets these requirements:

- **PHP** 8.2 or higher
- **CodeIgniter 4** 4.5 or higher
- **Composer** 2.x
- A database supported by CodeIgniter migrations (MySQL is recommended)

## Install via Composer

Install DEX as a dependency in your CodeIgniter 4 project:

```bash
composer require olajideolamide/dex
```

## Enable auto-discovery (recommended)

DEX integrates through CodeIgniter’s module auto-discovery. To load the package routes, events, and registrars, make sure your app discovers Composer packages.

In `app/Config/Modules.php`, ensure:

- `$discoverInComposer` is enabled
- `$aliases` includes `events`, `routes`, and `registrars`

Example:

```php
// app/Config/Modules.php

public $discoverInComposer = true;

public $aliases = [
    // ...keep your existing aliases...
    'events',
    'routes',
    'registrars',
];
```

{{< callout type="info" >}}
If you can’t (or don’t want to) use auto-discovery, you can manually include the package `Config/Events.php` and `Config/Routes.php` files from `vendor/olajideolamide/dex/src/Config/` in your app config.
{{< /callout >}}

## Run migrations

Create the DEX tables by running migrations:

```bash
php spark migrate --all
```

This creates:

- `dex_issues`
- `dex_occurrences`
- `dex_requests`

## Verify installation

Open your browser and navigate to:

```
http://your-app.test/dex/issues
```

If you changed the route prefix, the URL becomes:

```
http://your-app.test/<routePrefix>/issues
```

{{< callout type="warning" >}}
In `production`, the dashboard is blocked by default. To enable it safely, read the [Security Overview](/docs/security/overview/) first.
{{< /callout >}}

## Next steps

- [Quick Start](/docs/getting-started/quickstart/) — Trigger a test exception and see it in the dashboard
- [Configuration Overview](/docs/configuration/overview/) — Learn how to configure DEX using `DEX_*` environment variables

