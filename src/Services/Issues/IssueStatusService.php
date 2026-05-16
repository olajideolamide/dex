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

namespace Dex\Services\Issues;

use Dex\Domain\Exceptions\IssueNotFoundException;
use Dex\Repositories\IssueReadRepository;
use Dex\Repositories\IssueRepository;

final class IssueStatusService
{
    public function __construct(
        private readonly IssueReadRepository $issuesRead,
        private readonly IssueRepository $issuesWrite,
    ) {
    }

    /**
     * @throws IssueNotFoundException
     */
    public function resolve(int $issueId): array
    {
        $issue = $this->issuesRead->findIssue($issueId);
        if ($issue === null) {
            throw new IssueNotFoundException("Issue #{$issueId} not found");
        }

        $this->issuesWrite->resolveIssue($issueId);

        return $this->issuesRead->findIssue($issueId) ?? $issue;
    }

    /**
     * @throws IssueNotFoundException
     */
    public function ignore(int $issueId): array
    {
        $issue = $this->issuesRead->findIssue($issueId);
        if ($issue === null) {
            throw new IssueNotFoundException("Issue #{$issueId} not found");
        }

        $this->issuesWrite->ignoreIssue($issueId);

        return $this->issuesRead->findIssue($issueId) ?? $issue;
    }
}
