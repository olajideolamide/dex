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
use Dex\Tests\Support\Factories\LifecycleFactory;
use Dex\Tests\Support\Factories\RequestFactory;

/**
 * Journey 1 — a new exception creates an issue, occurrence, and request.
 *
 * @group database
 * @group journey
 */
final class NewIssueJourneyTest extends DexDatabaseTestCase
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

    public function testExceptionCreatesIssueOccurrenceAndRequest(): void
    {
        $fingerprint = 'journey-new-' . uniqid('', true);
        $requestId   = 'req-journey-new-' . uniqid('', true);

        // 1. Exception captured — upsert issue
        $issueId = $this->issues->upsertIssue([
            'fingerprint'   => $fingerprint,
            'level'         => 'error',
            'class'         => 'RuntimeException',
            'title'         => 'Test exception',
            'latest_path'   => '/api/test',
            'latest_method' => 'GET',
            'environment'   => 'testing',
        ]);

        // 2. Occurrence recorded
        $this->occurrences->recordOccurrence([
            'issue_id'    => $issueId,
            'request_id'  => $requestId,
            'happened_at' => date('Y-m-d H:i:s'),
            'message'     => 'Test exception message',
        ]);

        // 3. Request finalised
        $lifecycle = LifecycleFactory::apiValidationFailure();
        $row = RequestFactory::withException([
            'request_id'    => $requestId,
            'path'          => '/api/test',
            'lifecycle_json' => json_encode($lifecycle),
        ]);
        $this->requests->recordRequest($row);

        // Assert: 1 issue, 1 occurrence, 1 request
        $this->seeNumRecords(1, 'dex_issues', ['fingerprint' => $fingerprint]);
        $this->seeNumRecords(1, 'dex_occurrences', ['issue_id' => $issueId]);
        $this->seeNumRecords(1, 'dex_requests', ['request_id' => $requestId]);

        // Assert issue state
        $this->seeInDatabase('dex_issues', [
            'id'         => $issueId,
            'status'     => 'open',
            'times_seen' => 1,
        ]);

        // Assert occurrence points to request
        $this->seeInDatabase('dex_occurrences', [
            'issue_id'   => $issueId,
            'request_id' => $requestId,
        ]);

        // Assert request has error flags set
        $this->seeInDatabase('dex_requests', [
            'request_id'    => $requestId,
            'has_exception' => 1,
            'has_error'     => 1,
        ]);
    }
}
