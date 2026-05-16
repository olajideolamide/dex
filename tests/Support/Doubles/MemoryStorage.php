<?php

declare(strict_types=1);

namespace Dex\Tests\Support\Doubles;

use Dex\Contracts\StorageInterface;

final class MemoryStorage implements StorageInterface
{
    public array $issues = [];
    public array $occurrences = [];
    public array $requests = [];
    private int $issueId = 0;

    public function upsertIssue(array $issue): int
    {
        $this->issueId++;
        $issue['id'] = $this->issueId;
        $this->issues[] = $issue;
        return $this->issueId;
    }

    public function recordOccurrence(array $occurrence): void
    {
        $this->occurrences[] = $occurrence;
    }

    public function recordRequest(array $request): void
    {
        $this->requests[] = $request;
    }
}
