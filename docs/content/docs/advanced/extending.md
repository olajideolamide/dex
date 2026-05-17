---
title: "Extending DEX"
description: "Add more context to requests using breadcrumbs, spans, and manual captures."
weight: 43
---

DEX is intentionally small: it focuses on capturing exceptions and the request lifecycle.

The primary way to “extend” DEX today is to add more context at the points you care about.

## Add breadcrumbs

Breadcrumbs help you see what happened *before* an error:

```php
dex_breadcrumb('billing', 'Starting invoice generation', [
    'invoice_id' => (int) $invoiceId,
]);
```

## Add spans

Spans help you understand what took time:

```php
$spanId = dex_span_start('email.send', 'Send receipt email');

try {
    $mailer->sendReceipt($orderId);
} finally {
    dex_span_finish($spanId);
}
```

## Capture caught exceptions

If you catch an exception but still want it to show up as an issue:

```php
try {
    $service->doWork();
} catch (\Throwable $e) {
    dex_capture_exception($e);
}
```

## CLI: purge job

DEX currently ships with a single maintenance command:

```bash
php spark dex:purge
```

See [Purging & Retention](/docs/configuration/notifications/) for scheduling and configuration.

