---
title: "Database Permissions & Secrets"
description: "Keep DEX safe by limiting access to stored telemetry and managing credentials properly."
weight: 73
---

DEX stores issues, occurrences, and request snapshots in your database. Anyone who can read those tables can see error context.

## Use least privilege (where possible)

In most CodeIgniter applications, DEX uses the same database connection as your app. You still get value from least privilege by ensuring:

- The database user cannot access other databases on the server
- Backups are protected and access-controlled
- Production credentials are never committed to version control

## Be intentional about retention

Shorter retention reduces blast radius:

- [Purging & Retention](/docs/configuration/notifications/)

If you handle sensitive data, consider lowering:

- `DEX_PURGE_RETENTION_DAYS_REQUESTS`
- `DEX_PURGE_RETENTION_DAYS_OCCURRENCES`

## Don’t store secrets in snapshots

DEX scrubs known-sensitive field names, but it can’t guess everything.

Before enabling DEX UI in production:

- Review `DEX_SCRUB_FIELDS`
- Add app-specific secret field names
- Keep header capture conservative (occurrence header capture is off by default)

See:

- [PII & Data Handling](/docs/security/pii-and-data-handling/)

