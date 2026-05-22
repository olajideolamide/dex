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
 * Journey 3 — a resolved issue regresses when the same fingerprint is seen again.
 *
 * This is one of the most important DEX behaviours.
 *
 * @group database
 * @group journey
 */
final class ResolvedRegressionJourneyTest extends DexDatabaseTestCase
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

    public function testResolvedIssueRegressesWhenSeenAgain(): void
    {
        $fingerprint = 'journey-regression-' . uniqid('', true);
        $reqId1      = 'req-regr-1-' . uniqid('', true);
        $reqId2      = 'req-regr-2-' . uniqid('', true);

        // 1. Issue is created (open)
        $issueId = $this->issues->upsertIssue([
            'fingerprint' => $fingerprint,
            'level'       => 'error',
            'title'       => 'Checkout error',
            'environment' => 'testing',
        ]);
        $this->occurrences->recordOccurrence([
            'issue_id'    => $issueId,
            'request_id'  => $reqId1,
            'happened_at' => date('Y-m-d H:i:s'),
            'message'     => 'First occurrence',
        ]);
        $this->requests->recordRequest(RequestFactory::withException(['request_id' => $reqId1]));

        $this->seeInDatabase('dex_issues', ['id' => $issueId, 'status' => 'open']);

        // 2. Developer resolves the issue
        $this->issues->resolveIssue($issueId);
        $this->seeInDatabase('dex_issues', ['id' => $issueId, 'status' => 'resolved']);

        // 3. Same fingerprint appears again
        $sameId = $this->issues->upsertIssue([
            'fingerprint' => $fingerprint,
            'level'       => 'error',
            'title'       => 'Checkout error',
            'environment' => 'testing',
        ]);
        $this->occurrences->recordOccurrence([
            'issue_id'    => $sameId,
            'request_id'  => $reqId2,
            'happened_at' => date('Y-m-d H:i:s'),
            'message'     => 'Regression occurrence',
        ]);
        $this->requests->recordRequest(RequestFactory::withException(['request_id' => $reqId2]));

        // Assertions
        $this->assertSame($issueId, $sameId, 'Must return the original issue ID');
        $this->seeInDatabase('dex_issues', [
            'id'         => $issueId,
            'status'     => 'regression',
            'times_seen' => 2,
        ]);

        // Two occurrences must exist
        $this->seeNumRecords(2, 'dex_occurrences', ['issue_id' => $issueId]);

        // New occurrence must be linked
        $this->seeInDatabase('dex_occurrences', ['issue_id' => $issueId, 'request_id' => $reqId2]);
    }
}
