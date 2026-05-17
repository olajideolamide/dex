---
title: "Spans"
description: "Measure timed blocks of work inside a request."
weight: 53
---

Spans are timed blocks that help you understand where time went during a request.

DEX creates some spans automatically (for example, CI lifecycle spans), and you can add your own “manual spans” around code that matters.

## Start and finish a span

```php
$spanId = dex_span_start('billing.charge', 'Charge customer', [
    'provider' => 'stripe',
]);

try {
    $gateway->charge($amount);
} finally {
    dex_span_finish($spanId);
}
```

## Wrap a callback with `dex_span()`

If you prefer a cleaner API, use `dex_span()`:

```php
$result = dex_span('cache.warm', 'Warm cache entries', function () use ($cache) {
    return $cache->warm();
});
```

{{< callout type="tip" >}}
If `dex_span_start()` returns `null`, DEX is disabled or spans are disabled. `dex_span_finish()` safely no-ops in that case.
{{< /callout >}}

