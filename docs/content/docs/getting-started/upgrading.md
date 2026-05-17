---
title: "Upgrading"
description: "Upgrade DEX safely and keep your database schema in sync."
weight: 4
---

## Upgrade via Composer

To update DEX to the latest stable release:

```bash
composer update olajideolamide/dex
```

## Run migrations

After upgrading, run migrations to apply any schema changes:

```bash
php spark migrate --all
```

{{< callout type="warning" >}}
Back up your database before upgrading across major versions.
{{< /callout >}}

## Review release notes

DEX is meant to be backwards-compatible, but major versions may change behavior or defaults.

If a new version adds configuration options, you can set them via `DEX_*` environment variables:

- [Configuration Reference](/docs/configuration/config-reference/)

