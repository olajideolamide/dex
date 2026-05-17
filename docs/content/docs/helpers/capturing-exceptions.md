---
title: "Capturing Exceptions"
description: "Report caught exceptions to DEX so they show up as issues, even when you handle them."
weight: 51
---

DEX automatically captures **unhandled** exceptions (when enabled). But sometimes you catch an exception and still want it reported.

That’s what `dex_capture_exception()` is for.

## Capturing a caught exception

```php
try {
    $paymentGateway->charge($amount);
} catch (\Throwable $e) {
    // Report it to DEX
    dex_capture_exception($e);

    // Keep your existing handling
    log_message('error', $e->getMessage());
    return redirect()->back()->with('error', 'Payment failed.');
}
```

{{< callout type="tip" >}}
If you capture the exception and also rethrow it, you may end up with two captures (one manual, one unhandled). In most apps, you should do one or the other.
{{< /callout >}}

