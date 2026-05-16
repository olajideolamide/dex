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

use Dex\Support\DexRuntimePolicy;
use ErrorException;
use Throwable;

/**
 * Manages global exception and fatal error handlers.
 * Registers handlers once per process and coordinates capture with other services.
 */
final class ExceptionHandlerService
{
    private static bool $handlersRegistered = false;
    private mixed $previousExceptionHandler = null;
    private bool $exceptionCapturedThisRequest = false;
    private ?string $exceptionClassThisRequest = null;

    public function __construct(
        private readonly object $config,
        private readonly OccurrenceService $occurrenceService,
        private readonly RequestContextService $requestContext,
        private readonly RequestFinalizerService $finalizer,
        private readonly DexRuntimePolicy $runtimePolicy,
    ) {
    }

    /**
     * Register exception and shutdown handlers once per process (idempotent).
     */
    public function bootstrap(): void
    {
        if (self::$handlersRegistered) {
            return;
        }

        self::$handlersRegistered = true;

        if (!$this->runtimePolicy->isEnabled()) {
            return;
        }

        if (($this->config->captureUnhandledExceptions ?? true) === true) {
            $this->previousExceptionHandler = set_exception_handler(function (Throwable $e) {
                $this->handleUnhandledException($e);
            });
        }

        if (($this->config->captureShutdownFatals ?? true) === true) {
            register_shutdown_function(function () {
                $this->handleShutdownFatal();
            });
        }
    }

    /**
     * Record that an exception was captured in this request.
     */
    public function recordExceptionCaptured(string $exceptionClass): void
    {
        $this->exceptionCapturedThisRequest = true;
        $this->exceptionClassThisRequest = $exceptionClass;
    }

    /**
     * Check if exception was already captured in this request.
     */
    public function wasExceptionCaptured(): bool
    {
        return $this->exceptionCapturedThisRequest;
    }

    /**
     * Get the exception class if one was captured.
     */
    public function getCapturedExceptionClass(): ?string
    {
        return $this->exceptionClassThisRequest;
    }

    /**
     * Reset exception tracking for new request.
     */
    public function reset(): void
    {
        $this->exceptionCapturedThisRequest = false;
        $this->exceptionClassThisRequest = null;
    }

    /**
     * Capture an unhandled exception and delegate to the original handler.
     */
    private function handleUnhandledException(Throwable $e): void
    {
        // Never capture/store 404 exceptions as issues or crash-requests
        if ($this->isNotFoundThrowable($e)) {
            // Delegate to original handler (CI's), keep default behavior
            if (is_callable($this->previousExceptionHandler)) {
                try {
                    call_user_func($this->previousExceptionHandler, $e);
                } catch (Throwable) {
                    // ignore
                }
            }
            return;
        }

        $this->occurrenceService->captureException($e, true);
        $this->recordExceptionCaptured(get_class($e));

        // Store the failed request with the captured exception context.
        $this->maybeStoreCrashRequest(500);

        // Delegate to original handler (CI's), so default behavior stays the same
        if (is_callable($this->previousExceptionHandler)) {
            try {
                call_user_func($this->previousExceptionHandler, $e);
            } catch (Throwable) {
                // Avoid recursion/secondary crashes
            }
        }
    }

    /**
     * Capture fatal shutdown errors while avoiding internal/ignored paths.
     */
    private function handleShutdownFatal(): void
    {
        if (!$this->runtimePolicy->isEnabled()) {
            return;
        }

        if ($this->exceptionCapturedThisRequest) {
            return;
        }

        $err = error_get_last();
        if (! is_array($err)) {
            return;
        }

        $type = (int) ($err['type'] ?? 0);
        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

        if (! in_array($type, $fatalTypes, true)) {
            return;
        }

        $file = (string) ($err['file'] ?? '');
        $fileNorm = str_replace('\\', '/', $file);
        if (str_contains($fileNorm, '/vendor/jide/dex/')) {
            return;
        }

        try {
            $this->occurrenceService->captureException(
                new ErrorException(
                    (string) ($err['message'] ?? 'Fatal error'),
                    0,
                    $type,
                    $file,
                    (int) ($err['line'] ?? 0)
                ),
                true
            );
        } catch (Throwable) {
            return;
        }

        $this->recordExceptionCaptured(ErrorException::class);
        $this->maybeStoreCrashRequest(500);
    }

    /**
     * Store request snapshot if eligible and not yet recorded.
     */
    private function maybeStoreCrashRequest(int $statusCode = 500): void
    {
        $ctx = &$this->requestContext->getContextRef();
        if (!$ctx) {
            return; // no request context -> nothing to store
        }

        // prevent double insert if called more than once
        if (!empty($ctx['request_recorded'])) {
            return;
        }

        // Never track 404 requests
        if ($statusCode === 404) {
            return;
        }

        $endedAt = microtime(true);
        $ms = (int)round(($endedAt - (float)$ctx['started_at']) * 1000);

        [$shouldStore, $slowHit, $sampleHit] = $this->requestContext->shouldStoreRequest($statusCode, $ms);

        // Cache flags for snapshot/debug UI if you like
        $this->requestContext->storeSnapshotFlags($slowHit, $sampleHit);

        if (!$shouldStore) {
            return;
        }

        $memPeak = memory_get_peak_usage(true);

        $this->requestContext->storeFinalMetrics($statusCode, $ms, $memPeak);

        try {
            $this->finalizer->record($ctx, $statusCode, $ms, $memPeak, null);
        } catch (Throwable) {
            // Silently ignore to avoid cascading errors
            return;
        }

        $this->requestContext->markRecorded();
    }

    /**
     * Detect 404-style exceptions to ignore.
     */
    private function isNotFoundThrowable(Throwable $e): bool
    {
        if ($e instanceof \CodeIgniter\Exceptions\PageNotFoundException) {
            return true;
        }

        if (method_exists($e, 'getStatusCode')) {
            try {
                if ((int) $e->getStatusCode() === 404) {
                    return true;
                }
            } catch (Throwable) {
                return false;
            }
        }

        return (int) $e->getCode() === 404;
    }
}
