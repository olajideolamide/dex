---
title: "Breadcrumbs"
description: "Leave a small trail of events so you can see what happened before an error."
weight: 52
---

Breadcrumbs are lightweight “notes” attached to the current request.

They are most useful when you add them around decisions, external calls, or anything that helps you reconstruct what the request was doing right before it failed.

## Adding a breadcrumb

```php
dex_breadcrumb('checkout', 'Starting checkout', [
    'cart_items' => count($cartItems),
    'user_id' => (int) $userId,
]);
```

## Levels

You can use the `level` parameter to communicate severity:

```php
dex_breadcrumb('payment', 'Retrying gateway request', ['attempt' => 2], 'warning');
```

Common levels you’ll see in the UI:

- `info` (default)
- `warning`
- `error`

## Tips

- Keep breadcrumb data small and scrub-friendly.
- Prefer IDs and counts, not full payloads.
- If you store user data, consider updating `DEX_SCRUB_FIELDS` (and review your privacy policy).

