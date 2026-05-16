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
 * Thin adapter for CI lifecycle markers.
 */
final class LifecycleService
{
    private ?array $ctx = null;

    public function __construct(
        private readonly object $config,
        private readonly RequestLifecycleService $requestLifecycleService,
    ) {
    }

    public function setContext(?array &$ctx): void
    {
        $this->ctx = &$ctx;
        $this->requestLifecycleService->setContext($ctx);
    }

    public function mark(string $name, string $label, array $metadata = []): void
    {
        if (! $this->ctx || ! ($this->config->captureCiLifecycle ?? true)) {
            return;
        }

        $metadata['memory_bytes'] ??= memory_get_usage(true);
        $this->requestLifecycleService->checkpoint($name, $label, $this->smallMetadata($metadata), 'ci');

        if ($name === 'ci.post_controller') {
            $this->finishControllerSpan();
        }
    }

    public function checkpoint(string $name, string $label, array $metadata = [], string $source = 'system'): void
    {
        if (! $this->ctx) {
            return;
        }

        $this->requestLifecycleService->checkpoint($name, $label, $metadata, $source);
    }

    public function startControllerSpan(string $label, array $metadata = []): ?string
    {
        if (! $this->ctx || ! ($this->config->captureCiLifecycle ?? true)) {
            return null;
        }

        $spanId = $this->requestLifecycleService->startSpan(
            'controller.execution',
            $label,
            array_merge($metadata, [
                'confidence' => 'observed_start_approximate_end',
            ]),
            'ci',
            'auto'
        );

        if ($spanId !== null) {
            $this->ctx['controller_span_id'] = $spanId;
        }

        return $spanId;
    }

    public function routeMatched(array $metadata): void
    {
        if (! $this->ctx || ! ($this->config->captureCiLifecycle ?? true)) {
            return;
        }

        $metadata['method'] ??= $this->ctx['method'] ?? null;
        $metadata['path'] ??= $this->ctx['path'] ?? null;

        $spanId = $this->requestLifecycleService->startSpan('route.matched', 'Route matched', $metadata, 'ci', 'auto');
        if ($spanId !== null) {
            $this->requestLifecycleService->finishSpan($spanId, 'ok');
        }
    }

    public function finalize(int $durationMs): void
    {
    }

    private function finishControllerSpan(): void
    {
        $spanId = (string) ($this->ctx['controller_span_id'] ?? '');
        if ($spanId === '') {
            return;
        }

        $status = ! empty($this->ctx['had_error']) ? 'failed' : 'ok';
        $this->requestLifecycleService->finishSpan($spanId, $status);
        unset($this->ctx['controller_span_id']);
    }

    private function smallMetadata(array $metadata): array
    {
        $smallMetadata = [];
        foreach ($metadata as $key => $value) {
            if (count($smallMetadata) >= 12) {
                break;
            }

            if (is_scalar($value) || $value === null) {
                $smallMetadata[(string) $key] = $value;
                continue;
            }

            if (is_array($value)) {
                $smallMetadata[(string) $key] = array_slice($value, 0, 12);
            }
        }

        return $smallMetadata;
    }
}
