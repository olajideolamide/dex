<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dex\Services\Core;

/**
 * Keeps the public telemetry API and delegates collection to the lifecycle timeline.
 */
final class TelemetryService
{
    private ?array $ctx = null;

    public function __construct(
        private readonly object $config,
        private readonly RequestLifecycleService $lifecycleService,
    ) {
    }

    /**
     * Set the request context (called by Dex after context is initialized).
     */
    public function setContext(?array &$ctx): void
    {
        $this->ctx = &$ctx;
        $this->lifecycleService->setContext($ctx);
    }

    /**
     * Add a breadcrumb to the current request context.
     */
    public function addBreadcrumb(
        string $category,
        string $message,
        array $data = [],
        string $level = 'info'
    ): void {
        if (!$this->ctx) {
            return;
        }
        if (!($this->config->captureBreadcrumbs ?? true)) {
            return;
        }

        $origin = 'auto';
        if (isset($data['_origin'])) {
            $origin = (string) $data['_origin'];
            unset($data['_origin']);
        }

        $this->lifecycleService->breadcrumb($category, $message, $data, $level, $origin);
    }

    /**
     * Start a new span for request tracing.
     *
     */
    public function startSpan(string $op, ?string $description = null, array $tags = []): ?string
    {
        if (!$this->ctx) {
            return null;
        }
        if (!($this->config->captureSpans ?? true)) {
            return null;
        }

        $origin = 'auto';
        if (isset($tags['_origin'])) {
            $origin = (string) $tags['_origin'];
            unset($tags['_origin']);
        }

        $source = $this->sourceForSpan($op, $origin);
        if (isset($tags['_source'])) {
            $source = (string) $tags['_source'];
            unset($tags['_source']);
        }

        return $this->lifecycleService->startSpan(
            $op,
            $description,
            $tags,
            $source,
            $origin
        );
    }

    /**
     * Finish a span and compute its duration safely.
     */
    public function finishSpan(?string $id): void
    {
        if (!$this->ctx || !$id) {
            return;
        }
        $this->lifecycleService->finishSpan($id);
    }

    /**
     * Close any still-open spans so the replay can render cleanly.
     */
    public function closeOpenSpans(): void
    {
    }

    /**
     * Close lifecycle spans and the root transaction for this request.
     */
    public function finalizeSpans(): void
    {
        if (!$this->ctx) {
            return;
        }
    }

    private function sourceForSpan(string $operation, string $origin): string
    {
        if ($origin === 'manual') {
            return 'manual';
        }

        if (str_starts_with($operation, 'ci.') || $operation === 'controller.execution') {
            return 'ci';
        }

        if (str_starts_with($operation, 'http.')) {
            return 'system';
        }

        return 'manual';
    }
}
