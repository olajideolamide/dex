---
title: "Protecting the Dashboard"
description: "Lock down DEX UI access with allowlists and production-safe defaults."
weight: 71
---

DEX UI routes are protected by the `dex-ui` filter. The filter checks:

1. DEX is enabled
2. UI is enabled
3. If you’re in `production`, `allowInProduction` must be enabled
4. The client IP must pass the allowlist rules

## Step 1: Enable UI in production (only if you mean it)

In production, the UI is blocked unless you opt in:

```bash
DEX_ALLOW_IN_PRODUCTION=true
```

{{< callout type="warning" >}}
Only enable the UI in production if you also set a strict allowlist. Otherwise you may expose sensitive data.
{{< /callout >}}

## Step 2: Configure the allowlist

DEX uses `DEX_UI_ALLOWLIST` (CSV) for IPs and CIDR ranges:

```bash
# Office IP
DEX_UI_ALLOWLIST=203.0.113.10

# Office + VPN CIDR
DEX_UI_ALLOWLIST=203.0.113.10,10.0.0.0/8
```

If you want an additional “exact match” gate, you can also set `DEX_ALLOWED_IPS`:

```bash
DEX_ALLOWED_IPS=[\"203.0.113.10\"]
```

When `DEX_ALLOWED_IPS` is non-empty, the client IP must:

- be present in `DEX_ALLOWED_IPS` **and**
- match `DEX_UI_ALLOWLIST`

## Step 3: Decide how denies should behave

By default, DEX returns `404` on deny so it looks like the route doesn’t exist:

```bash
DEX_UI_STEALTH_DENY=true
```

If you prefer a normal `403` response:

```bash
DEX_UI_STEALTH_DENY=false
```

## Step 4: Consider a reverse proxy / extra auth

The allowlist is a good baseline, but for sensitive environments you’ll usually want one more layer:

- Basic auth at the reverse proxy
- An internal-only route (private subnet)
- VPN-only access

## Common pitfalls

- If you’re behind a proxy/CDN, make sure the request IP seen by CodeIgniter is the real client IP (otherwise allowlists may not behave as expected).
- Don’t forget to account for IPv6 if your environment uses it.

