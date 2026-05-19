---
title: "Security Overview"
description: "How to run DEX safely in production without exposing sensitive application data."
weight: 70
---

DEX stores error context and request snapshots in your own database. That’s powerful and it means you should treat the DEX dashboard like an internal admin tool.

This section covers practical, production-focused hardening steps.

## Golden rules

- **Do not expose DEX publicly.** Keep it behind an allowlist, VPN, admin network or authentication.
- **Assume request snapshots may contain sensitive data.** Configure scrubbing and be intentional about what you store.
- **Use least-privilege database permissions** for the account your app uses.

## What DEX does by default

Out of the box, DEX is conservative:

- The UI is blocked in `production` unless you explicitly allow it.
- UI access is allowlisted to `127.0.0.1,::1` by default.
- When access is denied, DEX returns `404` (“stealth deny”) by default.

## Next steps

- [Protecting the Dashboard]({{< relref "security/protecting-the-dashboard.md" >}}) — Allowlists, stealth deny, and production enablement
- [PII & Data Handling]({{< relref "security/pii-and-data-handling.md" >}}) — Scrubbing strategy and what to avoid storing
