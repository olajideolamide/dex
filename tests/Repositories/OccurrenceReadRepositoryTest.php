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

use Dex\Repositories\OccurrenceReadRepository;
use Dex\Tests\Support\DexDatabaseTestCase;

/**
 * @group database
 */
final class OccurrenceReadRepositoryTest extends DexDatabaseTestCase
{
    private OccurrenceReadRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OccurrenceReadRepository();
    }

    public function testListOccurrencesForIssue(): void
    {
        $issueId = $this->insertIssue();
        $otherId = $this->insertIssue(['fingerprint' => 'fp-other-' . uniqid('', true)]);

        $this->insertOccurrence(['issue_id' => $issueId, 'happened_at' => '2025-01-01 00:00:00']);
        $this->insertOccurrence(['issue_id' => $issueId, 'happened_at' => '2025-01-02 00:00:00']);
        $this->insertOccurrence(['issue_id' => $otherId, 'happened_at' => '2025-01-03 00:00:00']);

        $occurrences = $this->repository->listForIssue($issueId, 10);

        $this->assertCount(2, $occurrences);
        foreach ($occurrences as $occ) {
            $this->assertSame($issueId, (int) $occ['issue_id']);
        }
    }

    public function testOrderOccurrencesByHappenedAtDescending(): void
    {
        $issueId = $this->insertIssue();

        $this->insertOccurrence(['issue_id' => $issueId, 'happened_at' => '2025-01-01 00:00:00']);
        $this->insertOccurrence(['issue_id' => $issueId, 'happened_at' => '2025-03-01 00:00:00']);
        $this->insertOccurrence(['issue_id' => $issueId, 'happened_at' => '2025-02-01 00:00:00']);

        $occurrences = $this->repository->listForIssue($issueId, 10);

        $this->assertCount(3, $occurrences);
        $this->assertSame('2025-03-01 00:00:00', $occurrences[0]['happened_at']);
        $this->assertSame('2025-01-01 00:00:00', $occurrences[2]['happened_at']);
    }

    public function testFindLatestOccurrenceForIssue(): void
    {
        $issueId = $this->insertIssue();

        $this->insertOccurrence(['issue_id' => $issueId, 'happened_at' => '2025-01-01 00:00:00']);
        $this->insertOccurrence(['issue_id' => $issueId, 'happened_at' => '2025-06-01 00:00:00', 'message' => 'Latest occurrence']);

        $occurrence = $this->repository->findOccurrenceWithRequestForIssue($issueId, null);

        $this->assertNotNull($occurrence);
        $this->assertSame('Latest occurrence', $occurrence['message']);
    }

    public function testFindsOccurrencesForIssueWithRequest(): void
    {
        $issueId   = $this->insertIssue();
        $requestId = 'req-occ-join-' . uniqid('', true);

        $this->insertRequest(['request_id' => $requestId, 'path' => '/joined-path']);
        $this->insertOccurrence([
            'issue_id'    => $issueId,
            'request_id'  => $requestId,
            'happened_at' => date('Y-m-d H:i:s'),
        ]);

        $rows = $this->repository->listForIssueWithRequests($issueId, 10);

        $this->assertCount(1, $rows);
        $this->assertSame('/joined-path', $rows[0]['request_path']);
    }

    public function testHandleOccurrenceWithoutRequestId(): void
    {
        $issueId = $this->insertIssue();

        $this->insertOccurrence([
            'issue_id'   => $issueId,
            'request_id' => null,
            'message'    => 'No request',
        ]);

        $rows = $this->repository->listForIssueWithRequests($issueId, 10);

        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]['request_path'] ?? null);
    }

    public function testHandleMissingRequestRowGracefully(): void
    {
        $issueId = $this->insertIssue();

        // Occurrence references a request_id that does not exist in dex_requests
        $this->insertOccurrence([
            'issue_id'   => $issueId,
            'request_id' => 'req-ghost-12345',
            'message'    => 'Ghost request',
        ]);

        $rows = $this->repository->listForIssueWithRequests($issueId, 10);

        $this->assertCount(1, $rows);
        // The join should yield null request columns, not crash
        $this->assertNull($rows[0]['request_path'] ?? null);
    }

    public function testCountTotalForIssue(): void
    {
        $issueId = $this->insertIssue();

        $this->insertOccurrence(['issue_id' => $issueId]);
        $this->insertOccurrence(['issue_id' => $issueId]);
        $this->insertOccurrence(['issue_id' => $issueId]);

        $total = $this->repository->countTotalForIssue($issueId);
        $this->assertSame(3, $total);
    }
}
