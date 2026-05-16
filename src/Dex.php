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

namespace Dex;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Dex\Contracts\DexInterface;
use Dex\Adapters\CiRequestMetaFactory;
use Dex\Adapters\CiResponseApplier;
use Dex\DTO\RequestMeta;
use Dex\DTO\ResponseMeta;
use Dex\Services\Core\RequestContextService;
use Dex\Services\Core\OccurrenceService;
use Dex\Services\Core\TelemetryService;
use Dex\Services\Core\ExceptionHandlerService;
use Dex\Services\Core\QueryTrackingService;
use Dex\Services\Core\RequestFinalizerService;
use Dex\Services\Core\DatabaseStorageService;
use Dex\Services\Core\LifecycleService;
use Dex\Services\Core\RequestLifecycleService;
use Dex\Services\Support\PathService;
use Dex\Services\Support\FilterService;
use Dex\Support\DexRuntimePolicy;
use Dex\Adapters\CiRouterInfoProvider;
use Random\RandomException;
use Throwable;

/**
 * Dex - Main facade coordinating all monitoring services.
 * Delegates to specialized services via dependency injection.
 */
class Dex implements DexInterface
{
    private ?array $ctx = null;

    private readonly LifecycleService $lifecycleService;

    private readonly CiRouterInfoProvider $routerInfoProvider;

    public function __construct(
        private readonly object $config,
        private readonly DatabaseStorageService $storage,
        private readonly RequestContextService $contextService,
        private readonly OccurrenceService $occurrenceService,
        private readonly TelemetryService $telemetryService,
        private readonly ExceptionHandlerService $handlerService,
        private readonly QueryTrackingService $queryTracker,
        private readonly RequestFinalizerService $finalizer,
        private readonly PathService $pathService,
        private readonly FilterService $filterService,
        private readonly DexRuntimePolicy $runtimePolicy,
        ?LifecycleService $lifecycleService = null,
        ?CiRouterInfoProvider $routerInfoProvider = null
    ) {
        $this->lifecycleService = $lifecycleService ?? new LifecycleService($config, new RequestLifecycleService($config));
        $this->routerInfoProvider = $routerInfoProvider ?? new CiRouterInfoProvider();

        // Wire service cross-references
        $this->occurrenceService->setRequestContext($this->contextService);
    }

    /**
     * Register global exception/shutdown handlers (idempotent).
     */
    public function bootstrap(): void
    {
        $this->handlerService->bootstrap();
    }


    /**
     * Initialize request context from the CI request and start tracing.
     *
     * @throws RandomException
     */
    public function startRequest(RequestInterface $request, ResponseInterface $response): void
    {
        $meta = CiRequestMetaFactory::fromRequest($request, $this->config);
        $this->start($meta);
        $this->contextService->attachRawRequest($request);

        $ctx = &$this->contextService->getContextRef();
        if ($ctx) {
            $this->captureHttpSpanContext($request, $ctx);
        }
    }

    /**
     * Attach controller/route info and capture CI lifecycle spans/breadcrumbs.
     *
     */
    public function tagController(): void
    {
        $ctx = &$this->contextService->getContextRef();
        if (!$ctx) {
            return;
        }

        $info = $this->routerInfoProvider->getControllerInfo();
        if ($info->controller !== null || $info->action !== null) {
            $this->contextService->attachControllerInfo($info->controller ?? '', $info->action ?? '');
            $ctx['controller'] = $info->controller;
            $ctx['action'] = $info->action;
        }
        if ($info->route !== null) {
            $ctx['route'] = $info->route;
            $ctx['route_params'] = $info->params;
        }

        // Check if internal
        if (!$this->runtimePolicy->shouldRunController($ctx['controller'] ?? '')) {
            $this->contextService->reset();
            return;
        }

        $controller = (string) ($ctx['controller'] ?? '');
        $action = (string) ($ctx['action'] ?? '');
        $routeMetadata = [
            'method' => $ctx['method'] ?? null,
            'path' => $ctx['path'] ?? null,
            'controller' => $ctx['controller'] ?? null,
            'action' => $ctx['action'] ?? null,
            'route' => $ctx['route'] ?? null,
            'params' => $ctx['route_params'] ?? [],
            'route_options' => $info->routeOptions ?? null,
        ];

        $this->lifecycleService->routeMatched($routeMetadata);

        if (empty($ctx['controller_span_id']) && ($controller !== '' || $action !== '')) {
            $label = trim(($controller !== '' ? $controller : 'Controller') . ($action !== '' ? '::' . $action : ''));
            $this->lifecycleService->startControllerSpan($label);
        }
    }

    public function markLifecycleEvent(string $name, string $label, array $metadata = []): void
    {
        $this->lifecycleService->mark($name, $label, $metadata);
    }

    /**
     * Track a DB query unless it targets Dex internal tables.
     */
    public function trackDbQuery(object $query): void
    {
        $ctx = &$this->contextService->getContextRef();
        if (!$ctx) {
            return;
        }

        $sql = '';
        if (method_exists($query, 'getOriginalQuery')) {
            $sql = (string)$query->getOriginalQuery();
        } elseif (method_exists($query, 'getQuery')) {
            $sql = (string)$query->getQuery();
        } else {
            $sql = (string)$query;
        }

        // If we can't read SQL, track as usual
        if ($sql !== '' && stripos($sql, 'dex_') !== false) {
            $pattern = '/\b(from|join|update|into|delete\s+from)\s+(`?[\w-]+`?\.){0,2}`?(dex_[a-z0-9_]+)`?\b/i';
            if (preg_match($pattern, $sql) === 1) {
                return;
            }
        }

        $this->queryTracker->track($query, $ctx, $sql);
    }

    /**
     * Finalize the request, persist data, and apply response headers.
     */
    public function finishRequest(ResponseInterface $response): void
    {
        $status = method_exists($response, 'getStatusCode') ? (int)$response->getStatusCode() : 0;
        $meta = new ResponseMeta($status);

        $meta = $this->finish($meta);
        CiResponseApplier::apply($response, $meta);
    }


    /**
     * Start a request context from prebuilt metadata.
     *
     * @throws RandomException
     */
    public function start(RequestMeta $request): void
    {
        $this->contextService->reset();

        if (!$this->shouldRunRequest($request)) {
            return;
        }

        $this->handlerService->bootstrap();

        // Initialize context
        $this->contextService->start($request);
        $ctx = &$this->contextService->getContextRef();

        if (!$ctx) {
            return;
        }

        // Wire context references for services
        $this->ctx = &$ctx;
        $this->telemetryService->setContext($ctx);
        $this->filterService->setContext($ctx);
        $this->lifecycleService->setContext($ctx);

        $this->lifecycleService->checkpoint('request.started', 'Request started', [
            'method' => $ctx['method'],
            'path' => $ctx['path'],
        ]);

        // Start root transaction span
        $txnId = $this->telemetryService->startSpan('http.server', $ctx['method'] . ' ' . $ctx['path']);
        if ($txnId && isset($this->ctx)) {
            $this->ctx['txn_span_id'] = $txnId;
        }
    }

    public function shouldRunRequest(RequestMeta $request): bool
    {
        return $this->runtimePolicy->shouldRunRequest($request);
    }

    private function captureHttpSpanContext(RequestInterface $request, array &$ctx): void
    {
        try {
            $method = strtoupper((string) ($ctx['method'] ?? $request->getMethod() ?? ''));

            $queryKeys = [];
            if (method_exists($request, 'getGet')) {
                $get = $request->getGet();
                if (is_array($get)) {
                    $queryKeys = array_values(array_map('strval', array_keys($get)));
                }
            }

            $payloadKeys = [];
            if ($method === 'POST' && method_exists($request, 'getPost')) {
                $post = $request->getPost();
                if (is_array($post)) {
                    $payloadKeys = array_values(array_map('strval', array_keys($post)));
                }
            } elseif (in_array($method, ['PUT', 'PATCH', 'DELETE'], true) && method_exists($request, 'getRawInput')) {
                $raw = $request->getRawInput();
                if (is_array($raw)) {
                    $payloadKeys = array_values(array_map('strval', array_keys($raw)));
                }
            }

            $ctx['_http_span_context'] = [
                'method' => $ctx['method'] ?? null,
                'path' => $ctx['path'] ?? null,
                'content_type' => $request->getHeaderLine('Content-Type') ?: null,
                'content_length' => $request->getHeaderLine('Content-Length') ?: null,
                'query_keys' => array_slice($queryKeys, 0, 60),
                'payload_keys' => array_slice($payloadKeys, 0, 60),
            ];
        } catch (Throwable) {
            // never break host app
        }
    }


    /**
     * Close spans and persist request data only for error-linked requests.
     */
    public function finish(ResponseMeta $response): ResponseMeta
    {
        $ctx = &$this->contextService->getContextRef();
        if (!$ctx) {
            return $response;
        }

        $response = $response->withHeader($this->config->requestIdHeader, $ctx['request_id']);

        $endedAt = microtime(true);
        $ms = (int)round(($endedAt - (float)$ctx['started_at']) * 1000);

        $statusCode = $response->statusCode;

        if ($statusCode === 404) {
            $this->contextService->reset();
            return $response;
        }

        $memPeak = memory_get_peak_usage(true);
        $this->contextService->storeFinalMetrics($statusCode, $ms, $memPeak);

        // Decide if we should store this request
        [$shouldStore, $slowHit, $sampleHit] = $this->contextService->shouldStoreRequest($statusCode, $ms);
        $this->contextService->storeSnapshotFlags($slowHit, $sampleHit);

        if (!$shouldStore) {
            $this->contextService->reset();
            return $response;
        }

        try {
            $this->finalizer->record($ctx, $statusCode, $ms, $memPeak, $response);
        } catch (Throwable) {
            $this->contextService->handleDomainException(null);
            $this->contextService->reset();
            return $response;
        }

        $this->contextService->markRecorded();

        $this->contextService->reset();

        return $response;
    }


    // ====== Public API from DexInterface ======

    /**
     * Capture an exception as an occurrence/issue when eligible.
     * This is a public method that can be called from anywhere in the app to report exceptions to Dex.
     */
    public function captureException(Throwable $e, bool $unhandled = false): void
    {
        $this->occurrenceService->captureException($e, $unhandled);
        $this->handlerService->recordExceptionCaptured(get_class($e));
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
        $this->telemetryService->addBreadcrumb($category, $message, $data, $level);
    }

    /**
     * Start a new span for request tracing.
     *
     * @throws RandomException
     */
    public function startSpan(string $op, ?string $description = null, array $tags = []): ?string
    {
        return $this->telemetryService->startSpan($op, $description, $tags);
    }

    /**
     * Finish a span and compute its duration safely.
     */
    public function finishSpan(?string $id): void
    {
        $this->telemetryService->finishSpan($id);
    }

    /**
     * Public helper for other components (e.g. Log Handler) to check if path should be ignored.
     */
    public function shouldIgnorePath(string $path): bool
    {
        return $this->pathService->shouldIgnore($path);
    }
}
