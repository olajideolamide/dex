---
title: "Purging & Retention"
description: "Keep DEX tables under control with TTLs, row caps, and the dex:purge command."
weight: 12
---

DEX stores issues, occurrences, and request rows in your database. In active applications, those tables will grow over time.

To keep growth predictable, DEX includes a purge job you can run from the CLI:

```bash
php spark dex:purge
```

## What the purge job does

The purge job deletes old rows using two strategies:

- **TTL (retention days)** — delete anything older than N days
- **Max row caps** — keep only the newest N rows even if TTL is large

## Configure retention

Example `.env`:

```bash
DEX_PURGE_ENABLED=true

DEX_PURGE_RETENTION_DAYS_REQUESTS=14
DEX_PURGE_RETENTION_DAYS_OCCURRENCES=30
DEX_PURGE_RETENTION_DAYS_ISSUES=90
```

## Configure max row caps

Row caps act as a safety net:

```bash
DEX_PURGE_MAX_ROWS_REQUESTS=100000
DEX_PURGE_MAX_ROWS_OCCURRENCES=200000
DEX_PURGE_MAX_ROWS_ISSUES=50000
```

## Running the job (recommended schedule)

You can run purge on a schedule (cron, systemd timer, etc.). A good starting point is every 5–15 minutes on busy apps, or hourly on smaller apps.

Example:

```bash
php spark dex:purge --batch=1000 --max-runtime=30 --sleep-ms=25
```

## Concurrency protection (DB lock)

If you run purge from multiple servers, enable the DB lock to avoid concurrent runs:

```bash
DEX_PURGE_USE_DB_LOCK=true
DEX_PURGE_DB_LOCK_NAME=dex_purge
```

You can override the lock on a one-off run:

```bash
php spark dex:purge --no-lock
```

## Next steps

- [Configuration Reference](/docs/configuration/config-reference/) — Purge config options and defaults
- [Security Overview](/docs/security/overview/) — Why retention and access control matter

