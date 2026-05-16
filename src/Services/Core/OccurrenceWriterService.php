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
use Dex\Support\Scrubber;
use Dex\Support\DexTime;

/**
 * Writes occurrences and issues to storage with scrubbing.
 * Upserts issue records and records individual occurrence payloads.
 */
final readonly class OccurrenceWriterService
{
    public function __construct(
        private StorageInterface $storage,
        private object $config,
    ) {
    }

    /**
     * Upsert the issue and record a scrubbed occurrence payload.
     *
     * @param array $issueUpsert keys for storage->upsertIssue(), e.g:
     *   - fingerprint (required)
     *   - level (required)
     *   - title (required)
     *   - latest_path (recommended)
     *   - class (optional)
     *   - last_seen (optional; defaulted)
     */
    public function write(
        array $issueUpsert,
        string $message,
        array $payload,
        ?string $requestId
    ): void {
        if (! isset($issueUpsert['last_seen'])) {
            $issueUpsert['last_seen'] = DexTime::nowUtcString();
        }

        $issueId = $this->storage->upsertIssue($issueUpsert);

        $payload = $this->canonicalizePayload($payload, $requestId);

        // Scrub sensitive fields consistently for ALL occurrences.
        $scrubFields = (array)($this->config->scrubFields ?? []);
        if (!empty($scrubFields)) {
            $payload = Scrubber::scrub($payload, $scrubFields);
        }

        $this->storage->recordOccurrence([
            'issue_id' => $issueId,
            'happened_at' => DexTime::nowUtcString(),
            'message' => $message,
            'context' => Scrubber::safeJson($payload),
            'request_id' => $requestId,
        ]);
    }

    private function canonicalizePayload(array $payload, ?string $requestId): array
    {
        if ($requestId === null || $requestId === '') {
            return $payload;
        }

        unset(
            $payload['request'],
            $payload['http'],
            $payload['breadcrumbs'],
            $payload['spans'],
            $payload['lifecycle']
        );

        return $payload;
    }
}
