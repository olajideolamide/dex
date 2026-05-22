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

namespace Dex\Tests\Journeys;

use Dex\Repositories\IssueRepository;
use Dex\Repositories\OccurrenceRepository;
use Dex\Repositories\RequestRepository;
use Dex\Tests\Support\DexDatabaseTestCase;
use Dex\Tests\Support\Factories\RequestFactory;

/**
 * Journey 4 — ignored issue behaviour is consistent.
 *
 * Decision: an ignored issue stays ignored, but DEX still records the occurrence
 * so the developer can see historical data if they later unignore it.
 *
 * @group database
 * @group journey
 */
final class IgnoredIssueJourneyTest extends DexDatabaseTestCase
{
    private IssueRepository $issues;
    private OccurrenceRepository $occurrences;
    private RequestRepository $requests;

    protected function setUp(): void
    {
        parent::setUp();
        $this->issues      = new IssueRepository();
        $this->occurrences = new OccurrenceRepository();
        $this->requests    = new RequestRepository();
    }

    public function testIgnoredIssueBehaviorIsConsistent(): void
    {
        $fingerprint = 'journey-ignored-' . uniqid('', true);
        $reqId1      = 'req-ign-1-' . uniqid('', true);
        $reqId2      = 'req-ign-2-' . uniqid('', true);

        // 1. Issue created (open)
        $issueId = $this->issues->upsertIssue([
            'fingerprint' => $fingerprint,
            'level'       => 'warning',
            'title'       => 'Noisy warning',
            'environment' => 'testing',
        ]);
        $this->occurrences->recordOccurrence([
            'issue_id'    => $issueId,
            'request_id'  => $reqId1,
            'happened_at' => date('Y-m-d H:i:s'),
            'message'     => 'First occurrence',
        ]);
        $this->requests->recordRequest(RequestFactory::normal(['request_id' => $reqId1]));

        // 2. Developer ignores the issue
        $this->issues->ignoreIssue($issueId);
        $this->seeInDatabase('dex_issues', ['id' => $issueId, 'status' => 'ignored']);

        // 3. Same fingerprint appears again
        $sameId = $this->issues->upsertIssue([
            'fingerprint' => $fingerprint,
            'level'       => 'warning',
            'title'       => 'Noisy warning',
        ]);

        // Still record the occurrence so history is preserved
        $this->occurrences->recordOccurrence([
            'issue_id'    => $sameId,
            'request_id'  => $reqId2,
            'happened_at' => date('Y-m-d H:i:s'),
            'message'     => 'Second occurrence (ignored)',
        ]);
        $this->requests->recordRequest(RequestFactory::normal(['request_id' => $reqId2]));

        // Assertions
        $this->assertSame($issueId, $sameId);

        // Status stays ignored (not regressed)
        $this->seeInDatabase('dex_issues', [
            'id'     => $issueId,
            'status' => 'ignored',
        ]);

        // Both occurrences should be recorded
        $this->seeNumRecords(2, 'dex_occurrences', ['issue_id' => $issueId]);

        // Second occurrence is linked to its request
        $this->seeInDatabase('dex_occurrences', [
            'issue_id'   => $issueId,
            'request_id' => $reqId2,
        ]);
    }

    public function testIgnoredIssueTimesSeenIsIncremented(): void
    {
        $fingerprint = 'journey-ignored-times-' . uniqid('', true);

        $issueId = $this->issues->upsertIssue([
            'fingerprint' => $fingerprint,
            'level'       => 'info',
            'title'       => 'Ignored issue',
        ]);

        $this->issues->ignoreIssue($issueId);

        // See it again
        $this->issues->upsertIssue([
            'fingerprint' => $fingerprint,
            'level'       => 'info',
            'title'       => 'Ignored issue',
        ]);

        // times_seen should increment even for ignored issues
        $this->seeInDatabase('dex_issues', [
            'id'         => $issueId,
            'times_seen' => 2,
            'status'     => 'ignored',
        ]);
    }
}
