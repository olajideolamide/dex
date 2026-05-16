<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Dex\Config;

use CodeIgniter\Config\BaseService;
use Config\Database;
use Dex\Adapters\CiCacheStore;
use Dex\Adapters\CiDbBucketExpressionProvider;
use Dex\Adapters\CiHttpContextProvider;
use Dex\Adapters\CiRequestPathProvider;
use Dex\Adapters\CiRouterInfoProvider;
use Dex\Contracts\DexInterface;
use Dex\Dex as DexClient;
use Dex\Orchestrators\IssuesOrchestrator;
use Dex\Orchestrators\PurgeDataOrchestrator;
use Dex\Repositories\IssueReadRepository;
use Dex\Repositories\IssueRepository;
use Dex\Repositories\OccurrenceReadRepository;
use Dex\Repositories\RequestReadRepository;
use Dex\Services\Core\DataPurgeService;
use Dex\Services\Core\DatabaseStorageService;
use Dex\Services\Core\ExceptionHandlerService;
use Dex\Services\Core\LifecycleService;
use Dex\Services\Core\OccurrenceService;
use Dex\Services\Core\OccurrenceWriterService;
use Dex\Services\Core\QueryTrackingService;
use Dex\Services\Core\RateLimiterService;
use Dex\Services\Core\RequestContextService;
use Dex\Services\Core\RequestFinalizerService;
use Dex\Services\Core\RequestLifecycleService;
use Dex\Services\Core\TelemetryService;
use Dex\Services\Issues\IssueShowBreakdownsService;
use Dex\Services\Issues\IssueShowMetricsService;
use Dex\Services\Issues\IssueShowViewPrepService;
use Dex\Services\Issues\IssuesDetailService;
use Dex\Services\Issues\IssuesListService;
use Dex\Services\Issues\IssuesSparklineService;
use Dex\Services\Issues\IssuesTrendService;
use Dex\Services\Issues\IssueStatusService;
use Dex\Services\Support\FilterService;
use Dex\Services\Support\PathService;
use Dex\Support\CachedConfigProvider;
use Dex\Support\DexRuntimePolicy;

class Services extends BaseService
{
    public static function dex(bool $getShared = true): DexInterface
    {
        if ($getShared) {
            return static::getSharedInstance('dex');
        }

        $config = static::configProvider()->get();
        $storage = static::databaseStorage(false);
        $contextService = new RequestContextService($config);
        $rateLimiter = new RateLimiterService($config, new CiCacheStore());
        $requestLifecycleService = new RequestLifecycleService($config);
        $runtimePolicy = new DexRuntimePolicy($config);
        $telemetryService = new TelemetryService($config, $requestLifecycleService);
        $occurrenceWriter = new OccurrenceWriterService($storage, $config);
        $occurrenceService = new OccurrenceService(
            $config,
            $occurrenceWriter,
            $rateLimiter,
            new CiHttpContextProvider(),
            $requestLifecycleService,
            $runtimePolicy
        );
        $finalizer = new RequestFinalizerService($config, $storage, $requestLifecycleService);
        $handlerService = new ExceptionHandlerService($config, $occurrenceService, $contextService, $finalizer, $runtimePolicy);
        $queryTracker = new QueryTrackingService($config, $requestLifecycleService, $runtimePolicy);
        $lifecycleService = new LifecycleService($config, $requestLifecycleService);
        $pathService = new PathService($config, new CiRequestPathProvider());
        $filterService = new FilterService($config, $pathService);

        return new DexClient(
            $config,
            $storage,
            $contextService,
            $occurrenceService,
            $telemetryService,
            $handlerService,
            $queryTracker,
            $finalizer,
            $pathService,
            $filterService,
            $runtimePolicy,
            $lifecycleService,
            new CiRouterInfoProvider()
        );
    }

    public static function databaseStorage(bool $getShared = true): DatabaseStorageService
    {
        if ($getShared) {
            return static::getSharedInstance('databaseStorage');
        }

        return new DatabaseStorageService();
    }

    public static function issuesOrchestrator(bool $getShared = true): IssuesOrchestrator
    {
        if ($getShared) {
            return static::getSharedInstance('issuesOrchestrator');
        }

        return new IssuesOrchestrator(
            static::issuesListService(false),
            static::issuesSparklineService(false),
            static::issuesTrendService(false),
            static::issuesDetailService(false),
            static::issueShowViewPrepService(false),
            static::issueShowMetricsService(false),
            static::issueShowBreakdownsService(false),
            static::issueStatusService(false)
        );
    }

    public static function issueShowViewPrepService(bool $getShared = true): IssueShowViewPrepService
    {
        if ($getShared) {
            return static::getSharedInstance('issueShowViewPrepService');
        }

        return new IssueShowViewPrepService();
    }

    public static function issueShowMetricsService(bool $getShared = true): IssueShowMetricsService
    {
        if ($getShared) {
            return static::getSharedInstance('issueShowMetricsService');
        }

        return new IssueShowMetricsService(
            new OccurrenceReadRepository(),
            new CiDbBucketExpressionProvider()
        );
    }

    public static function issueShowBreakdownsService(bool $getShared = true): IssueShowBreakdownsService
    {
        if ($getShared) {
            return static::getSharedInstance('issueShowBreakdownsService');
        }

        return new IssueShowBreakdownsService(
            new OccurrenceReadRepository(),
            new RequestReadRepository()
        );
    }

    public static function issuesListService(bool $getShared = true): IssuesListService
    {
        if ($getShared) {
            return static::getSharedInstance('issuesListService');
        }

        return new IssuesListService(new IssueReadRepository());
    }

    public static function issuesSparklineService(bool $getShared = true): IssuesSparklineService
    {
        if ($getShared) {
            return static::getSharedInstance('issuesSparklineService');
        }

        return new IssuesSparklineService(
            new OccurrenceReadRepository(),
            new CiDbBucketExpressionProvider()
        );
    }

    public static function issuesTrendService(bool $getShared = true): IssuesTrendService
    {
        if ($getShared) {
            return static::getSharedInstance('issuesTrendService');
        }

        return new IssuesTrendService(new OccurrenceReadRepository());
    }

    public static function issuesDetailService(bool $getShared = true): IssuesDetailService
    {
        if ($getShared) {
            return static::getSharedInstance('issuesDetailService');
        }

        return new IssuesDetailService(
            new IssueReadRepository(),
            new OccurrenceReadRepository(),
            new RequestReadRepository()
        );
    }

    public static function issueStatusService(bool $getShared = true): IssueStatusService
    {
        if ($getShared) {
            return static::getSharedInstance('issueStatusService');
        }

        return new IssueStatusService(
            new IssueReadRepository(),
            new IssueRepository()
        );
    }

    public static function configProvider(bool $getShared = true): CachedConfigProvider
    {
        if ($getShared) {
            return static::getSharedInstance('configProvider');
        }

        return new CachedConfigProvider();
    }

    public static function dataPurgeService(bool $getShared = true): DataPurgeService
    {
        if ($getShared) {
            return static::getSharedInstance('dataPurgeService');
        }

        return new DataPurgeService(Database::connect(), static::configProvider()->get());
    }

    public static function purgeDataOrchestrator(bool $getShared = true): PurgeDataOrchestrator
    {
        if ($getShared) {
            return static::getSharedInstance('purgeDataOrchestrator');
        }

        return new PurgeDataOrchestrator(static::dataPurgeService(false));
    }
}
