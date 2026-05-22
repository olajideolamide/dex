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

use Dex\Repositories\IssueReadRepository;
use Dex\Tests\Support\DexDatabaseTestCase;

/**
 * @group database
 */
final class IssueReadRepositoryTest extends DexDatabaseTestCase
{
    private IssueReadRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new IssueReadRepository();
    }

    public function testListIssuesOrderedByLastSeenDescending(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-older', 'last_seen' => '2025-01-01 00:00:00']);
        $this->insertIssue(['fingerprint' => 'fp-newer', 'last_seen' => '2025-06-01 00:00:00']);

        $issues = $this->repository->listIssues(null, '', 10);

        $this->assertCount(2, $issues);
        $this->assertSame('fp-newer', $issues[0]['fingerprint']);
        $this->assertSame('fp-older', $issues[1]['fingerprint']);
    }

    public function testFilterByStatusOpen(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-open',     'status' => 'open']);
        $this->insertIssue(['fingerprint' => 'fp-resolved', 'status' => 'resolved']);

        $issues = $this->repository->listIssues('open', '', 10);

        $this->assertCount(1, $issues);
        $this->assertSame('fp-open', $issues[0]['fingerprint']);
    }

    public function testFilterByStatusResolved(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-open-2',     'status' => 'open']);
        $this->insertIssue(['fingerprint' => 'fp-resolved-2', 'status' => 'resolved']);

        $issues = $this->repository->listIssues('resolved', '', 10);

        $this->assertCount(1, $issues);
        $this->assertSame('fp-resolved-2', $issues[0]['fingerprint']);
    }

    public function testFilterByStatusIgnored(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-ign-a', 'status' => 'open']);
        $this->insertIssue(['fingerprint' => 'fp-ign-b', 'status' => 'ignored']);

        $issues = $this->repository->listIssues('ignored', '', 10);

        $this->assertCount(1, $issues);
        $this->assertSame('fp-ign-b', $issues[0]['fingerprint']);
    }

    public function testFilterByStatusRegressed(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-reg-a', 'status' => 'open']);
        $this->insertIssue(['fingerprint' => 'fp-reg-b', 'status' => 'regression']);

        $issues = $this->repository->listIssues('regressed', '', 10);

        $this->assertCount(1, $issues);
        $this->assertSame('fp-reg-b', $issues[0]['fingerprint']);
    }

    public function testSearchByTitle(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-search-a', 'title' => 'Database connection error']);
        $this->insertIssue(['fingerprint' => 'fp-search-b', 'title' => 'Unrelated issue']);

        $issues = $this->repository->listIssues(null, 'Database connection', 10);

        $this->assertCount(1, $issues);
        $this->assertSame('fp-search-a', $issues[0]['fingerprint']);
    }

    public function testSearchByClass(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-class-a', 'class' => 'App\\Exceptions\\FooException']);
        $this->insertIssue(['fingerprint' => 'fp-class-b', 'class' => 'App\\Exceptions\\BarException']);

        $issues = $this->repository->listIssues(null, 'FooException', 10);

        $this->assertCount(1, $issues);
        $this->assertSame('fp-class-a', $issues[0]['fingerprint']);
    }

    public function testSearchByPath(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-path-a', 'latest_path' => '/api/checkout']);
        $this->insertIssue(['fingerprint' => 'fp-path-b', 'latest_path' => '/api/profile']);

        $issues = $this->repository->listIssues(null, 'checkout', 10);

        $this->assertCount(1, $issues);
        $this->assertSame('fp-path-a', $issues[0]['fingerprint']);
    }

    public function testCountIssuesByStatus(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-cnt-a', 'status' => 'open']);
        $this->insertIssue(['fingerprint' => 'fp-cnt-b', 'status' => 'open']);
        $this->insertIssue(['fingerprint' => 'fp-cnt-c', 'status' => 'resolved']);
        $this->insertIssue(['fingerprint' => 'fp-cnt-d', 'status' => 'ignored']);
        $this->insertIssue(['fingerprint' => 'fp-cnt-e', 'status' => 'regression']);

        $counts = $this->repository->countByStatus();

        $this->assertSame(5, $counts['total']);
        $this->assertSame(2, $counts['open']);
        $this->assertSame(1, $counts['resolved']);
        $this->assertSame(1, $counts['ignored']);
        $this->assertSame(1, $counts['regressed']);
    }

    public function testFindIssueById(): void
    {
        $issueId = $this->insertIssue(['fingerprint' => 'fp-find', 'title' => 'Findable issue']);

        $issue = $this->repository->findIssue($issueId);

        $this->assertNotNull($issue);
        $this->assertSame($issueId, (int) $issue['id']);
        $this->assertSame('Findable issue', $issue['title']);
    }

    public function testReturnNullWhenIssueDoesNotExist(): void
    {
        $issue = $this->repository->findIssue(99999);

        $this->assertNull($issue);
    }

    public function testPaginationLimitAndOffset(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->insertIssue([
                'fingerprint' => "fp-page-{$i}",
                'last_seen'   => "2025-01-0{$i} 00:00:00",
            ]);
        }

        $first = $this->repository->listIssues(null, '', 2, 0);
        $second = $this->repository->listIssues(null, '', 2, 2);

        $this->assertCount(2, $first);
        $this->assertCount(2, $second);
        // Ensure no overlap
        $this->assertNotSame($first[0]['fingerprint'], $second[0]['fingerprint']);
    }

    public function testCountIssues(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-count-1']);
        $this->insertIssue(['fingerprint' => 'fp-count-2']);
        $this->insertIssue(['fingerprint' => 'fp-count-3']);

        $total = $this->repository->countIssues(null, '');
        $this->assertSame(3, $total);
    }
}
