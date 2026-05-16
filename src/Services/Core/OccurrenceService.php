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

use CodeIgniter\Exceptions\PageNotFoundException;
use Dex\Adapters\CiHttpContextProvider;
use Dex\Support\Fingerprint;
use Dex\Support\DexRuntimePolicy;
use Dex\Domain\Exceptions\DexException;
use ReflectionClass;
use Throwable;

/**
 * Captures exceptions as occurrences/issues.
 * Handles filtering, rate limiting, payload construction, and persistence coordination.
 */
final class OccurrenceService
{
    private RequestContextService $requestContext;

    public function __construct(
        private readonly object $config,
        private readonly OccurrenceWriterService $writer,
        private readonly RateLimiterService $rateLimiter,
        private readonly CiHttpContextProvider $httpContextProvider,
        private readonly RequestLifecycleService $requestLifecycleService,
        private readonly DexRuntimePolicy $runtimePolicy,
    ) {
    }

    /**
     * Wire the request context service (called by Dex facade).
     */
    public function setRequestContext(RequestContextService $service): void
    {
        $this->requestContext = $service;
    }

    /**
     * Capture an exception as an occurrence/issue when eligible.
     */
    public function captureException(Throwable $e, bool $unhandled = false): void
    {
        if (!$this->runtimePolicy->shouldRunContext($this->runtimeContext())) {
            return;
        }

        // Never capture 404 exceptions
        if ($this->isNotFoundThrowable($e)) {
            return;
        }

        // Skip if trace indicates it originated from dex namespace/vendor
        if ($this->traceTouchesIgnoredNamespace($e)) {
            return;
        }

        $ctx = &$this->requestContext->getContextRef();
        if ($ctx) {
            $this->requestContext->markError();
            $this->requestLifecycleService->setContext($ctx);
            $this->requestLifecycleService->exception($e, [
                'unhandled' => $unhandled,
            ]);
        }

        $fingerprint = Fingerprint::fromException($e);
        if ($this->rateLimiter->isLimited($fingerprint)) {
            return;
        }

        $title = mb_substr((string)$e->getMessage(), 0, 180);
        $shortClass = (new ReflectionClass($e))->getShortName();
        $requestInfo = $this->requestContextForOccurrence();

        $trace = array_slice($e->getTrace(), 0, 25);

        $payload = [
            'level' => 'error',
            'exception' => [
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'unhandled' => $unhandled,
                'trace' => $trace,
            ],
            'request' => $requestInfo,
            'http' => $this->httpContextForOccurrence(),
            'tags' => $this->tagsForOccurrence(),
            'lifecycle' => $ctx['lifecycle'] ?? [],
        ];

        $issue = [
            'fingerprint' => $fingerprint,
            'level' => 'error',
            'class' => $shortClass,
            'title' => $title,
            'latest_path' => $requestInfo['path'] ?? null,
            'latest_method' => $requestInfo['method'] ?? null,
            'environment' => defined('ENVIRONMENT') ? (string) ENVIRONMENT : null,
        ];

        try {
            $this->writer->write(
                $issue,
                (string)$e->getMessage(),
                $payload,
                $ctx['request_id'] ?? null
            );
        } catch (DexException $ex) {
            $this->requestContext->handleDomainException($ex);
        }
    }

    /**
     * Pull request context fields into the occurrence payload.
     */
    private function requestContextForOccurrence(): array
    {
        $ctx = $this->requestContext->getContext();
        if (!$ctx) {
            return [];
        }

        return [
            'request_id' => $ctx['request_id'] ?? null,
            'method' => $ctx['method'] ?? null,
            'path' => $ctx['path'] ?? null,
            'controller' => $ctx['controller'] ?? null,
            'action' => $ctx['action'] ?? null,
            'ip' => $ctx['ip'] ?? null,
            'user_agent' => $ctx['user_agent'] ?? null,
            'db_count' => $ctx['db_count'] ?? null,
            'db_time_ms' => $ctx['db_time_ms'] ?? null,
        ];
    }

    /**
     * Build compact tags used for UI breakdowns.
     */
    private function tagsForOccurrence(): array
    {
        $ctx = $this->requestContext->getContext();
        $tags = [
            'environment' => defined('ENVIRONMENT') ? (string)ENVIRONMENT : null,
            'php' => PHP_VERSION,
            'sapi' => PHP_SAPI,
        ];

        if ($ctx) {
            $tags['controller'] = $ctx['controller'] ?? null;
            $tags['action'] = $ctx['action'] ?? null;
            $tags['method'] = $ctx['method'] ?? null;
        }

        // Drop nulls
        return array_filter($tags, static fn($v) => $v !== null && $v !== '');
    }

    /**
     * Capture HTTP context for error occurrences when enabled.
     */
    private function httpContextForOccurrence(): array
    {
        if (($this->config->captureHttpOnError ?? true) === false) {
            return [];
        }

        $includeHeaders = (bool)($this->config->captureRequestHeadersOnError ?? false);
        $maxHeaders = (int)($this->config->maxCapturedHeaders ?? 40);
        $maxValLen = (int)($this->config->maxCapturedHeaderValueLength ?? 800);

        return $this->httpContextProvider->build($includeHeaders, $maxHeaders, $maxValLen);
    }

    /**
     * Detect traces originating from ignored namespaces or Dex internals.
     */
    private function traceTouchesIgnoredNamespace(Throwable $e): bool
    {
        $namespaces = (array)($this->config->ignoreNamespaces ?? []);
        if (empty($namespaces)) {
            return false;
        }

        foreach ($e->getTrace() as $t) {
            $class = (string)($t['class'] ?? '');
            if ($class === '') {
                continue;
            }

            foreach ($namespaces as $ns) {
                if ($ns && str_starts_with($class, $ns)) {
                    return true;
                }
            }
        }

        // Also check origin file (common when no class frame)
        $file = $e->getFile();
        if ($file) {
            $fileNorm = str_replace('\\', '/', $file);
            if (str_contains($fileNorm, '/vendor/jide/dex/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect 404-style exceptions to ignore.
     */
    private function isNotFoundThrowable(Throwable $e): bool
    {
        // CI4 404
        if ($e instanceof PageNotFoundException) {
            return true;
        }

        // Some codebases throw custom HTTP exceptions; try statusCode if present
        if (method_exists($e, 'getStatusCode')) {
            try {
                if ((int)$e->getStatusCode() === 404) {
                    return true;
                }
            } catch (Throwable) {
                // ignore
            }
        }

        // Fallback: if code is explicitly 404, treat as not found
        return ((int)$e->getCode() === 404);
    }

    private function runtimeContext(): ?array
    {
        $context = $this->requestContext->getContext();
        if (!is_array($context)) {
            $context = [];
        }

        if (($context['user_agent'] ?? '') === '') {
            $context['user_agent'] = (string) ($this->httpContextProvider->currentUserAgent() ?? '');
        }

        return $context;
    }
}
