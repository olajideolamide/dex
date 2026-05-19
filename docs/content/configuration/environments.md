---
title: "Environments"
description: "Configure DEX safely across development, staging, and production."
weight: 11
---

DEX uses CodeIgniter’s `ENVIRONMENT` constant (usually driven by `CI_ENVIRONMENT`) to decide how strict it should be about UI access.

## Development and staging

In non-production environments, DEX UI can be enabled (and is allowlisted to localhost by default).

You’ll usually leave these on:

```bash
DEX_ENABLED=true
DEX_UI_ENABLED=true
```

## Production

In `production`, DEX blocks the UI unless you explicitly allow it:

```bash
DEX_ALLOW_IN_PRODUCTION=true
```

{{< callout type="warning" >}}
Only enable DEX UI in production if you also configure a strict allowlist. See [Protecting the Dashboard](/security/protecting-the-dashboard/).
{{< /callout >}}

## A practical pattern

Most teams run with:

- DEX always enabled (so issues are captured)
- UI only enabled on trusted networks (VPN / office IPs)

Example production env:

```bash
DEX_ENABLED=true
DEX_UI_ENABLED=true
DEX_ALLOW_IN_PRODUCTION=true
DEX_UI_ALLOWLIST=10.0.0.0/8,203.0.113.10
```

## Next steps

- [Security Overview](/security/overview/) — Production hardening checklist
- [Configuration Reference](/configuration/config-reference/) — All options and env keys
