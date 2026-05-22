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
 * Journey 2 — the same exception fingerprint appears twice.
 *
 * @group database
 * @group journey
 */
final class RepeatedIssueJourneyTest extends DexDatabaseTestCase
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

    public function testRepeatedExceptionIncrementsIssueAndCreatesOccurrence(): void
    {
        $fingerprint = 'journey-repeat-' . uniqid('', true);
        $reqId1      = 'req-repeat-1-' . uniqid('', true);
        $reqId2      = 'req-repeat-2-' . uniqid('', true);

        $issueData = [
            'fingerprint' => $fingerprint,
            'level'       => 'error',
            'title'       => 'Repeated exception',
            'environment' => 'testing',
        ];

        // First occurrence
        $issueId = $this->issues->upsertIssue($issueData);
        $this->occurrences->recordOccurrence([
            'issue_id'    => $issueId,
            'request_id'  => $reqId1,
            'happened_at' => '2025-01-01 10:00:00',
            'message'     => 'First time',
        ]);
        $this->requests->recordRequest(RequestFactory::withException(['request_id' => $reqId1]));

        // Capture first_seen before second hit
        $rowBefore = $this->db->table('dex_issues')->where('id', $issueId)->get()->getRowArray();
        $firstSeen = $rowBefore['first_seen'];

        // Second occurrence — same fingerprint

        $sameId = $this->issues->upsertIssue(array_merge($issueData, ['last_seen' => '2025-01-02 10:00:00']));
        $this->occurrences->recordOccurrence([
            'issue_id'    => $sameId,
            'request_id'  => $reqId2,
            'happened_at' => '2025-01-02 10:00:00',
            'message'     => 'Second time',
        ]);
        $this->requests->recordRequest(RequestFactory::withException(['request_id' => $reqId2]));

        // Assertions
        $this->assertSame($issueId, $sameId, 'Should return the same issue ID');
        $this->seeNumRecords(1, 'dex_issues', ['fingerprint' => $fingerprint]);
        $this->seeNumRecords(2, 'dex_occurrences', ['issue_id' => $issueId]);

        $this->seeInDatabase('dex_issues', [
            'id'         => $issueId,
            'times_seen' => 2,
            'status'     => 'open',
        ]);

        // first_seen must not change
        $rowAfter = $this->db->table('dex_issues')->where('id', $issueId)->get()->getRowArray();
        $this->assertSame($firstSeen, $rowAfter['first_seen'], 'first_seen should not change on repeat');

        // last_seen must differ from first_seen (we explicitly set it to 2025-01-02)
        $this->assertNotSame($firstSeen, $rowAfter['last_seen'], 'last_seen should be updated');
    }
}
