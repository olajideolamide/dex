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
use Dex\Repositories\OccurrenceReadRepository;
use Dex\Repositories\RequestReadRepository;

/**
 * Fetches detailed information for a single issue.
 * Includes occurrence history, request context, and event paging metadata.
 */
final class IssuesDetailService
{
    public function __construct(
        private readonly IssueReadRepository $issues,
        private readonly OccurrenceReadRepository $occurrences,
        private readonly RequestReadRepository $requests,
    ) {
    }

    /**
     * @throws IssueNotFoundException
     */
    public function getIssueDetail(int $issueId, int $selectedOccurrenceId): array
    {
        $issue = $this->issues->findIssue($issueId);
        if ($issue === null) {
            throw new IssueNotFoundException("Issue #{$issueId} not found");
        }

        $selected = $this->occurrences->findOccurrenceWithRequestForIssue($issueId, $selectedOccurrenceId);
        if ($selected === null && $selectedOccurrenceId > 0) {
            $selected = $this->occurrences->findOccurrenceWithRequestForIssue($issueId, null);
        }

        $request = null;
        if (is_array($selected) && ! empty($selected['request_id'])) {
            $request = $this->requests->findLatestByRequestId((string) $selected['request_id']);
        }

        return [
            'issue' => $issue,
            'occurrences' => $selected ? [$selected] : [],
            'selected' => $selected,
            'request' => $request,
            'eventPager' => $this->buildEventPager($issueId, $selected),
        ];
    }

    /**
     * @return array{total:int, position:int, newerOccurrenceId:?int, olderOccurrenceId:?int}
     */
    private function buildEventPager(int $issueId, ?array $selected): array
    {
        if (! is_array($selected)) {
            return [
                'total' => 0,
                'position' => 0,
                'newerOccurrenceId' => null,
                'olderOccurrenceId' => null,
            ];
        }

        $occurrenceId = (int) ($selected['id'] ?? 0);
        $happenedAt = (string) ($selected['happened_at'] ?? '');
        if ($occurrenceId <= 0 || $happenedAt === '') {
            return [
                'total' => 0,
                'position' => 0,
                'newerOccurrenceId' => null,
                'olderOccurrenceId' => null,
            ];
        }

        return [
            'total' => $this->occurrences->countTotalForIssue($issueId),
            'position' => $this->occurrences->findOccurrencePositionForIssue($issueId, $occurrenceId, $happenedAt),
            'newerOccurrenceId' => $this->occurrences->findAdjacentOccurrenceIdForIssue($issueId, $occurrenceId, $happenedAt, 'newer'),
            'olderOccurrenceId' => $this->occurrences->findAdjacentOccurrenceIdForIssue($issueId, $occurrenceId, $happenedAt, 'older'),
        ];
    }
}
