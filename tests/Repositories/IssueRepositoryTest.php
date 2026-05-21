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

namespace Dex\Tests\Repositories;

use Dex\Repositories\IssueRepository;
use Dex\Tests\Support\DexDatabaseTestCase;

/**
 * @group database
 */
final class IssueRepositoryTest extends DexDatabaseTestCase
{
    private IssueRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new IssueRepository();
    }

    public function testCreatesIssue(): void
    {
        $issueId = $this->repository->upsertIssue([
            'fingerprint'   => 'fingerprint-new',
            'level'         => 'error',
            'title'         => 'New issue',
            'latest_path'   => '/health',
            'latest_method' => 'GET',
            'environment'   => 'testing',
        ]);

        $this->assertGreaterThan(0, $issueId);
        $this->seeInDatabase('dex_issues', [
            'id'            => $issueId,
            'fingerprint'   => 'fingerprint-new',
            'status'        => 'open',
            'times_seen'    => 1,
            'latest_method' => 'GET',
            'environment'   => 'testing',
        ]);
    }

    public function testSameFingerprintIncrementsExistingIssue(): void
    {
        $fp = 'fingerprint-increment';

        $issueId = $this->repository->upsertIssue([
            'fingerprint' => $fp,
            'level'       => 'error',
            'title'       => 'Original',
        ]);

        $sameId = $this->repository->upsertIssue([
            'fingerprint' => $fp,
            'level'       => 'error',
            'title'       => 'Original',
        ]);

        $this->assertSame($issueId, $sameId);
        $this->seeNumRecords(1, 'dex_issues', ['fingerprint' => $fp]);
        $this->seeInDatabase('dex_issues', ['id' => $issueId, 'times_seen' => 2]);
    }

    public function testUpdatesLastSeenForRepeatedIssue(): void
    {
        $fp = 'fingerprint-last-seen';
        $firstTime = '2025-01-01 00:00:00';

        // Insert with an old last_seen
        $issueId = $this->repository->upsertIssue([
            'fingerprint' => $fp,
            'level'       => 'error',
            'title'       => 'Issue',
            'last_seen'   => $firstTime,
        ]);

        $this->repository->upsertIssue([
            'fingerprint' => $fp,
            'level'       => 'error',
            'title'       => 'Issue',
        ]);

        $row = $this->db->table('dex_issues')->where('id', $issueId)->get()->getRowArray();
        $this->assertNotNull($row);
        $this->assertNotSame($firstTime, $row['last_seen']);
    }

    public function testUpdatesLatestPathAndMethod(): void
    {
        $fp = 'fingerprint-path';

        $issueId = $this->repository->upsertIssue([
            'fingerprint'   => $fp,
            'level'         => 'error',
            'title'         => 'Issue',
            'latest_path'   => '/original',
            'latest_method' => 'GET',
        ]);

        $this->repository->upsertIssue([
            'fingerprint'   => $fp,
            'level'         => 'error',
            'title'         => 'Issue',
            'latest_path'   => '/updated',
            'latest_method' => 'POST',
        ]);

        $this->seeInDatabase('dex_issues', [
            'id'            => $issueId,
            'latest_path'   => '/updated',
            'latest_method' => 'POST',
        ]);
    }

    public function testKeepsFirstSeenUnchanged(): void
    {
        $fp = 'fingerprint-first-seen';
        $firstTime = '2025-01-01 00:00:00';

        $issueId = $this->repository->upsertIssue([
            'fingerprint' => $fp,
            'level'       => 'error',
            'title'       => 'Issue',
            'first_seen'  => $firstTime,
        ]);

        $this->repository->upsertIssue([
            'fingerprint' => $fp,
            'level'       => 'error',
            'title'       => 'Issue',
        ]);

        $row = $this->db->table('dex_issues')->where('id', $issueId)->get()->getRowArray();
        $this->assertNotNull($row);
        
        // first_seen must not change
        $this->assertSame($firstTime, $row['first_seen']);
    }

    public function testHandlesOpenIssue(): void
    {
        $fp = 'fingerprint-open';

        $issueId = $this->repository->upsertIssue([
            'fingerprint' => $fp,
            'level'       => 'error',
            'title'       => 'Open issue',
        ]);

        // Seeing it again should keep it open
        $this->repository->upsertIssue([
            'fingerprint' => $fp,
            'level'       => 'error',
            'title'       => 'Open issue',
        ]);

        $this->seeInDatabase('dex_issues', ['id' => $issueId, 'status' => 'open']);
    }

    public function testHandlesIgnoredIssue(): void
    {
        $fp = 'fingerprint-ignored';

        $issueId = $this->repository->upsertIssue([
            'fingerprint' => $fp,
            'level'       => 'error',
            'title'       => 'Ignored issue',
        ]);

        $this->repository->ignoreIssue($issueId);

        $this->seeInDatabase('dex_issues', ['id' => $issueId, 'status' => 'ignored']);

        // Seeing it again — ignored stays ignored (does not regress)
        $this->repository->upsertIssue([
            'fingerprint' => $fp,
            'level'       => 'error',
            'title'       => 'Ignored issue',
        ]);

        $this->seeInDatabase('dex_issues', ['id' => $issueId, 'status' => 'ignored']);
    }

    public function testResolvedIssueBecomesRegressed(): void
    {
        $fp = 'fingerprint-regression';

        $issueId = $this->repository->upsertIssue([
            'fingerprint' => $fp,
            'level'       => 'error',
            'title'       => 'Original issue',
        ]);

        $this->repository->resolveIssue($issueId);
        $this->seeInDatabase('dex_issues', ['id' => $issueId, 'status' => 'resolved']);

        $sameIssueId = $this->repository->upsertIssue([
            'fingerprint' => $fp,
            'level'       => 'critical',
            'title'       => 'Updated issue',
            'latest_path' => '/retry',
        ]);

        $this->assertSame($issueId, $sameIssueId);
        $this->seeNumRecords(1, 'dex_issues', ['fingerprint' => $fp]);
        $this->seeInDatabase('dex_issues', [
            'id'         => $issueId,
            'status'     => 'regression',
            'times_seen' => 2,
            'level'      => 'critical',
            'latest_path' => '/retry',
        ]);
    }

    public function testDoesNotCreateDuplicateFingerprint(): void
    {
        $fp = 'fingerprint-no-duplicate';

        $this->repository->upsertIssue(['fingerprint' => $fp, 'level' => 'error', 'title' => 'Issue']);
        $this->repository->upsertIssue(['fingerprint' => $fp, 'level' => 'error', 'title' => 'Issue']);
        $this->repository->upsertIssue(['fingerprint' => $fp, 'level' => 'error', 'title' => 'Issue']);

        $this->seeNumRecords(1, 'dex_issues', ['fingerprint' => $fp]);
    }

    /** Legacy test kept for coverage */
    public function testUpsertIssueInsertsNewIssue(): void
    {
        $issueId = $this->repository->upsertIssue([
            'fingerprint'   => 'fingerprint-legacy-new',
            'level'         => 'error',
            'title'         => 'New issue',
            'latest_path'   => '/health',
            'latest_method' => 'GET',
            'environment'   => 'testing',
        ]);

        $this->assertGreaterThan(0, $issueId);
        $this->seeInDatabase('dex_issues', [
            'id'          => $issueId,
            'fingerprint' => 'fingerprint-legacy-new',
            'status'      => 'open',
            'times_seen'  => 1,
        ]);
    }

    /** Legacy test kept for coverage */
    public function testUpsertIssueReopensResolvedIssueAsRegression(): void
    {
        $issueId = $this->repository->upsertIssue([
            'fingerprint' => 'fingerprint-legacy-regression',
            'level'       => 'error',
            'title'       => 'Original issue',
        ]);

        $this->repository->resolveIssue($issueId);

        $sameIssueId = $this->repository->upsertIssue([
            'fingerprint' => 'fingerprint-legacy-regression',
            'level'       => 'critical',
            'title'       => 'Updated issue',
            'latest_path' => '/retry',
        ]);

        $this->assertSame($issueId, $sameIssueId);
        $this->seeNumRecords(1, 'dex_issues', ['fingerprint' => 'fingerprint-legacy-regression']);
        $this->seeInDatabase('dex_issues', [
            'id'         => $issueId,
            'status'     => 'regression',
            'times_seen' => 2,
        ]);
    }
}
