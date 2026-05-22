---
title: "Installation"
description: "Install DEX via Composer and integrate it with your CodeIgniter 4 application."
weight: 1
---

## Requirements

Before installing DEX, make sure your environment meets these requirements:

- **PHP** 8.2 or higher
- **CodeIgniter 4.3.5 or higher
- **Composer** 2.x
- A database supported by CodeIgniter 4 (e.g. MySQL, MariaDB, SQLite)

## Install via Composer

Install DEX as a dependency in your CodeIgniter 4 project:

```bash
composer require olajideolamide/dex
```

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
http://your-app.test/dex
```

If you changed the route prefix, the URL becomes:

```
http://your-app.test/<routePrefix>
```

{{< callout type="warning" >}}
In `production`, the dashboard is blocked by default. To enable it safely, read the [Security Overview]({{< relref "security/overview.md" >}})
{{< /callout >}}

## Next steps

- [Quick Start]({{< relref "getting-started/quickstart.md" >}}) — Trigger a test exception and see it in the dashboard
- [Configuration Overview]({{< relref "configuration/overview.md" >}}) — Learn how to configure DEX using `DEX_*` environment variables
