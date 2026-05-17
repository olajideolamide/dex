---
title: "Reverse Proxy, HTTPS, and Headers"
description: "Recommended settings when DEX is accessed through a proxy, load balancer, or CDN."
weight: 72
---

In production, you’ll often access DEX through a reverse proxy (Nginx, Caddy, Apache, Cloudflare, a load balancer, etc.).

That setup can affect two things that matter for DEX:

- **Client IP detection** (for allowlists)
- **Request ID propagation** (for correlation)

## Make sure DEX sees the real client IP

DEX UI access is protected by IP allowlists, and those checks use the request IP that CodeIgniter reports.

If your app sits behind a proxy, ensure CodeIgniter is configured to trust that proxy so `getIPAddress()` reflects the real client IP (not the proxy’s IP).

{{< callout type="warning" >}}
Don’t blindly trust `X-Forwarded-For` from the public internet. Only trust forwarding headers from proxies you control.
{{< /callout >}}

## Use HTTPS for the dashboard

Treat the dashboard as an internal admin surface:

- Serve it over HTTPS
- Avoid exposing it on public networks
- Prefer VPN / private subnets / allowlisted networks

## Propagate request IDs end-to-end

DEX reads an incoming request ID header and echoes it back on the response.

If your infrastructure already injects a correlation ID header, configure DEX to use the same header name:

```bash
DEX_REQUEST_ID_HEADER=X-Request-Id
```

If you use a different header (for example `X-Correlation-Id`):

```bash
DEX_REQUEST_ID_HEADER=X-Correlation-Id
```

## Extra authentication (recommended)

IP allowlisting is a good baseline. For many production setups, add one more layer:

- Basic auth at the reverse proxy, or
- An internal-only route (private subnet), or
- VPN-only access

