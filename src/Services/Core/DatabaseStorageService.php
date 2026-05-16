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

namespace Dex\Services\Core;

use Dex\Contracts\StorageInterface;
use Dex\Repositories\IssueRepository;
use Dex\Repositories\OccurrenceRepository;
use Dex\Repositories\RequestRepository;

/**
 * Facade over write-side repositories for persistence.
 * Coordinates writing to issues, occurrences, and requests.
 */
final class DatabaseStorageService implements StorageInterface
{
    private IssueRepository $issues;
    private OccurrenceRepository $occurrences;
    private RequestRepository $requests;

    public function __construct(
        ?IssueRepository $issues = null,
        ?OccurrenceRepository $occurrences = null,
        ?RequestRepository $requests = null
    ) {
        $this->issues = $issues ?? new IssueRepository();
        $this->occurrences = $occurrences ?? new OccurrenceRepository();
        $this->requests = $requests ?? new RequestRepository();
    }

    public function upsertIssue(array $issue): int
    {
        return $this->issues->upsertIssue($issue);
    }

    public function recordOccurrence(array $occurrence): void
    {
        $this->occurrences->recordOccurrence($occurrence);
    }

    public function recordRequest(array $request): void
    {
        $this->requests->recordRequest($request);
    }
}
