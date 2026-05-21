<?php

declare(strict_types=1);

namespace Dex\Tests\Repositories;

use Dex\Repositories\IssueRepository;
use Dex\Tests\Support\DexDatabaseTestCase;

final class IssueRepositoryTest extends DexDatabaseTestCase
{
    public function testUpsertIssueInsertsNewIssue(): void
    {
        $repository = new IssueRepository();

        $issueId = $repository->upsertIssue([
            'fingerprint' => 'fingerprint-new',
            'level' => 'error',
            'title' => 'New issue',
            'latest_path' => '/health',
            'latest_method' => 'GET',
            'environment' => 'testing',
        ]);

        $this->assertGreaterThan(0, $issueId);
        $this->seeInDatabase('dex_issues', [
            'id' => $issueId,
            'fingerprint' => 'fingerprint-new',
            'status' => 'open',
            'times_seen' => 1,
            'latest_method' => 'GET',
            'environment' => 'testing',
        ]);
    }

    public function testUpsertIssueReopensResolvedIssueAsRegression(): void
    {
        $repository = new IssueRepository();

        $issueId = $repository->upsertIssue([
            'fingerprint' => 'fingerprint-regression',
            'level' => 'error',
            'title' => 'Original issue',
        ]);

        $repository->resolveIssue($issueId);

        $sameIssueId = $repository->upsertIssue([
            'fingerprint' => 'fingerprint-regression',
            'level' => 'critical',
            'title' => 'Updated issue',
            'latest_path' => '/retry',
        ]);

        $this->assertSame($issueId, $sameIssueId);
        $this->seeNumRecords(1, 'dex_issues', ['fingerprint' => 'fingerprint-regression']);
        $this->seeInDatabase('dex_issues', [
            'id' => $issueId,
            'status' => 'regression',
            'times_seen' => 2,
            'level' => 'critical',
            'title' => 'Updated issue',
            'latest_path' => '/retry',
        ]);
    }
}
