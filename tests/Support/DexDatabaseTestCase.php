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

namespace Dex\Tests\Support;

use CodeIgniter\Test\DatabaseTestTrait;
use Dex\Support\DexTime;

abstract class DexDatabaseTestCase extends DexTestCase
{
    use DatabaseTestTrait;

    /** The namespace that owns the migrations CI4 should run. */
    protected $namespace = 'Dex';

    /** Run migrations before each test class. */
    protected $migrate = true;

    /** Drop and re-run migrations fresh for every test, ensuring isolation. */
    protected $refresh = true;

    // -------------------------------------------------------------------------
    // Insert helpers
    // -------------------------------------------------------------------------

    /**
     * Insert a row into dex_issues and return the inserted ID.
     *
     * @param array<string, mixed> $overrides
     */
    protected function insertIssue(array $overrides = []): int
    {
        $defaults = [
            'fingerprint'   => 'fp-' . uniqid('', true),
            'level'         => 'error',
            'class'         => 'RuntimeException',
            'title'         => 'Test issue',
            'latest_path'   => '/test',
            'latest_method' => 'GET',
            'environment'   => 'testing',
            'status'        => 'open',
            'times_seen'    => 1,
            'first_seen'    => DexTime::nowUtcString(),
            'last_seen'     => DexTime::nowUtcString(),
        ];

        $data = array_merge($defaults, $overrides);
        $this->db->table('dex_issues')->insert($data);

        return (int) $this->db->insertID();
    }

    /**
     * Insert a row into dex_requests and return the inserted ID.
     *
     * @param array<string, mixed> $overrides
     */
    protected function insertRequest(array $overrides = []): int
    {
        $defaults = [
            'request_id'            => 'req-' . uniqid('', true),
            'method'                => 'GET',
            'path'                  => '/test',
            'status_code'           => 200,
            'duration_ms'           => 100,
            'mem_peak'              => 1024000,
            'db_count'              => 0,
            'db_time_ms'            => 0,
            'controller'            => null,
            'action'                => null,
            'snapshot_json'         => null,
            'lifecycle_json'        => null,
            'lifecycle_version'     => 2,
            'has_error'             => 0,
            'has_exception'         => 0,
            'slow_request'          => 0,
            'slow_query_count'      => 0,
            'slowest_query_ms'      => 0,
            'lifecycle_event_count' => 0,
            'manual_span_count'     => 0,
            'breadcrumb_count'      => 0,
            'created_at'            => DexTime::nowUtcString(),
        ];

        $data = array_merge($defaults, $overrides);
        $this->db->table('dex_requests')->insert($data);

        return (int) $this->db->insertID();
    }

    /**
     * Insert a row into dex_occurrences and return the inserted ID.
     *
     * @param array<string, mixed> $overrides
     */
    protected function insertOccurrence(array $overrides = []): int
    {
        $defaults = [
            'issue_id'   => 0,
            'request_id' => null,
            'happened_at' => DexTime::nowUtcString(),
            'message'    => 'Test occurrence message',
            'context'    => null,
        ];

        $data = array_merge($defaults, $overrides);
        $this->db->table('dex_occurrences')->insert($data);

        return (int) $this->db->insertID();
    }

    // -------------------------------------------------------------------------
    // Assertion helpers
    // -------------------------------------------------------------------------

    /**
     * Assert that a row matching $where exists in $table.
     *
     * @param array<string, mixed> $where
     */
    protected function assertTableHas(string $table, array $where): void
    {
        $this->seeInDatabase($table, $where);
    }

    /**
     * Fetch a row from $table matching $where, JSON-decode the $column, and return the result.
     *
     * @param array<string, mixed> $where
     * @return array<string, mixed>
     */
    protected function decodeJsonColumn(string $table, string $column, array $where): array
    {
        $row = $this->db->table($table)->where($where)->get()->getRowArray();

        $this->assertNotNull($row, "No row found in {$table} matching the given conditions.");

        $raw = $row[$column] ?? null;
        $this->assertNotNull($raw, "Column {$column} is null in the matched row.");

        $decoded = json_decode((string) $raw, true);
        $this->assertIsArray($decoded, "Column {$column} could not be decoded as JSON.");

        return $decoded;
    }
}
