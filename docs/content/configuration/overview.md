---
title: "Configuration Overview"
description: "How DEX configuration works, where values come from, and how to set them."
weight: 9
---

DEX ships with sensible defaults, and you override them using **environment variables**.

## Where configuration comes from

DEX reads its configuration from two places, in this order:

1. **Defaults** in the package config class
2. **`DEX_*` environment variables** (from `.env`, server env, Docker, etc.)

## Naming

Each config property maps to an environment variable using this rule:

- `routePrefix` → `DEX_ROUTE_PREFIX`
- `captureShutdownFatals` → `DEX_CAPTURE_SHUTDOWN_FATALS`

## Types and casting

DEX casts environment values based on the default property type:

- **Booleans**: `true`, `false`, `1`, `0`, `yes`, `no`
- **Integers / floats**: `1000`, `48_000` (use plain numbers in env: `48000`)
- **Arrays**: either JSON (`["a","b"]`) or comma-separated (`a,b`)

{{< callout type="info" >}}
For array options, JSON is the most reliable format (especially when values contain spaces).
{{< /callout >}}

## Example `.env`

```bash
# Enable DEX
DEX_ENABLED=true

# Put the dashboard under /ops/dex instead of /dex
DEX_ROUTE_PREFIX=ops/dex

# Allow dashboard access in production (still protected by allowlists)
DEX_ALLOW_IN_PRODUCTION=true

# Allow your office VPN CIDR
DEX_UI_ALLOWLIST=10.0.0.0/8,192.168.0.0/16
```

## Next steps

- [Configuration Reference](/configuration/config-reference/) — Every supported config option (defaults, types, examples)
- [Securing DEX](/security/overview/) — Production hardening guide
