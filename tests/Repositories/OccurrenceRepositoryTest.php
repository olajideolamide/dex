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

use Dex\Repositories\OccurrenceRepository;
use Dex\Tests\Support\DexDatabaseTestCase;

/**
 * @group database
 */
final class OccurrenceRepositoryTest extends DexDatabaseTestCase
{
    private OccurrenceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OccurrenceRepository();
    }

    public function testCreatesOccurrenceLinkedToIssue(): void
    {
        $issueId   = $this->insertIssue();
        $requestId = 'req-occ-' . uniqid('', true);
        $now       = date('Y-m-d H:i:s');

        $this->repository->recordOccurrence([
            'issue_id'    => $issueId,
            'request_id'  => $requestId,
            'happened_at' => $now,
            'message'     => 'Something broke',
            'context'     => null,
        ]);

        $this->seeInDatabase('dex_occurrences', [
            'issue_id'   => $issueId,
            'request_id' => $requestId,
            'message'    => 'Something broke',
        ]);
    }

    public function testStoresRequestId(): void
    {
        $issueId   = $this->insertIssue();
        $requestId = 'req-occ-rid-' . uniqid('', true);

        $this->repository->recordOccurrence([
            'issue_id'    => $issueId,
            'request_id'  => $requestId,
            'happened_at' => date('Y-m-d H:i:s'),
            'message'     => 'Error',
        ]);

        $this->seeInDatabase('dex_occurrences', ['request_id' => $requestId]);
    }

    public function testStoresHappenedAt(): void
    {
        $issueId    = $this->insertIssue();
        $happenedAt = '2025-03-15 12:34:56';

        $this->repository->recordOccurrence([
            'issue_id'    => $issueId,
            'happened_at' => $happenedAt,
            'message'     => 'Error',
        ]);

        $this->seeInDatabase('dex_occurrences', ['issue_id' => $issueId, 'happened_at' => $happenedAt]);
    }

    public function testStoresMessage(): void
    {
        $issueId = $this->insertIssue();
        $message = 'Call to a member function foo() on null';

        $this->repository->recordOccurrence([
            'issue_id'    => $issueId,
            'happened_at' => date('Y-m-d H:i:s'),
            'message'     => $message,
        ]);

        $this->seeInDatabase('dex_occurrences', ['issue_id' => $issueId, 'message' => $message]);
    }

    public function testStoresContextJson(): void
    {
        $issueId = $this->insertIssue();
        $context = json_encode(['user_id' => 42, 'route' => '/checkout']);

        $this->repository->recordOccurrence([
            'issue_id'    => $issueId,
            'happened_at' => date('Y-m-d H:i:s'),
            'message'     => 'Error with context',
            'context'     => $context,
        ]);

        $stored = $this->decodeJsonColumn('dex_occurrences', 'context', ['issue_id' => $issueId]);
        $this->assertSame(42, $stored['user_id']);
    }

    public function testCanStoreLargeContextPayload(): void
    {
        $issueId     = $this->insertIssue();
        $largeContext = json_encode(['data' => str_repeat('x', 10000)]);

        $this->repository->recordOccurrence([
            'issue_id'    => $issueId,
            'happened_at' => date('Y-m-d H:i:s'),
            'message'     => 'Error with large context',
            'context'     => $largeContext,
        ]);

        $this->seeInDatabase('dex_occurrences', ['issue_id' => $issueId]);
    }
}
