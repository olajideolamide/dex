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

namespace Dex\Tests\Database;

use Dex\Tests\Support\DexDatabaseTestCase;

/**
 * @group database
 */
final class MigrationTest extends DexDatabaseTestCase
{
    // -------------------------------------------------------------------------
    // Tables exist
    // -------------------------------------------------------------------------

    public function testCreatesDexIssuesTable(): void
    {
        $this->assertTrue($this->db->tableExists('dex_issues'), 'dex_issues table should exist after migration');
    }

    public function testCreatesDexOccurrencesTable(): void
    {
        $this->assertTrue($this->db->tableExists('dex_occurrences'), 'dex_occurrences table should exist after migration');
    }

    public function testCreatesDexRequestsTable(): void
    {
        $this->assertTrue($this->db->tableExists('dex_requests'), 'dex_requests table should exist after migration');
    }

    // -------------------------------------------------------------------------
    // Critical columns — dex_issues
    // -------------------------------------------------------------------------

    public function testDexIssuesHasCriticalColumns(): void
    {
        $columns = $this->db->getFieldNames('dex_issues');
        $required = [
            'id', 'fingerprint', 'level', 'class', 'title',
            'latest_path', 'latest_method', 'environment',
            'status', 'times_seen', 'first_seen', 'last_seen',
        ];

        foreach ($required as $column) {
            $this->assertContains($column, $columns, "dex_issues should have column: {$column}");
        }
    }

    // -------------------------------------------------------------------------
    // Critical columns — dex_occurrences
    // -------------------------------------------------------------------------

    public function testDexOccurrencesHasCriticalColumns(): void
    {
        $columns = $this->db->getFieldNames('dex_occurrences');
        $required = [
            'id', 'issue_id', 'request_id', 'happened_at', 'message', 'context',
        ];

        foreach ($required as $column) {
            $this->assertContains($column, $columns, "dex_occurrences should have column: {$column}");
        }
    }

    // -------------------------------------------------------------------------
    // Critical columns — dex_requests
    // -------------------------------------------------------------------------

    public function testDexRequestsHasCriticalColumns(): void
    {
        $columns = $this->db->getFieldNames('dex_requests');
        $required = [
            'id', 'request_id', 'method', 'path', 'status_code',
            'duration_ms', 'mem_peak', 'db_count', 'db_time_ms',
            'controller', 'action', 'snapshot_json', 'lifecycle_json',
            'lifecycle_version', 'has_error', 'has_exception', 'slow_request',
            'slow_query_count', 'slowest_query_ms', 'lifecycle_event_count',
            'manual_span_count', 'breadcrumb_count', 'created_at',
        ];

        foreach ($required as $column) {
            $this->assertContains($column, $columns, "dex_requests should have column: {$column}");
        }
    }

    // -------------------------------------------------------------------------
    // Unique constraints
    // -------------------------------------------------------------------------

    public function testCreatesUniqueIssueFingerprint(): void
    {
        $fp = 'unique-fingerprint-test-' . uniqid('', true);
        $now = date('Y-m-d H:i:s');

        $this->db->table('dex_issues')->insert([
            'fingerprint'   => $fp,
            'level'         => 'error',
            'title'         => 'First',
            'status'        => 'open',
            'times_seen'    => 1,
            'first_seen'    => $now,
            'last_seen'     => $now,
        ]);

        $this->expectException(\Throwable::class);

        $this->db->table('dex_issues')->insert([
            'fingerprint'   => $fp,
            'level'         => 'error',
            'title'         => 'Duplicate',
            'status'        => 'open',
            'times_seen'    => 1,
            'first_seen'    => $now,
            'last_seen'     => $now,
        ]);
    }

    public function testCreatesUniqueRequestId(): void
    {
        $requestId = 'req-unique-' . uniqid('', true);
        $now = date('Y-m-d H:i:s');

        $row = [
            'request_id'  => $requestId,
            'method'      => 'GET',
            'path'        => '/test',
            'duration_ms' => 10,
            'mem_peak'    => 1024,
            'created_at'  => $now,
        ];

        $this->db->table('dex_requests')->insert($row);

        $this->expectException(\Throwable::class);
        $this->db->table('dex_requests')->insert($row);
    }

    // -------------------------------------------------------------------------
    // Rollback and re-run
    // -------------------------------------------------------------------------

    public function testMigrationCanRollbackAndRerun(): void
    {
        // Tables exist at this point (migration ran in setUp)
        $this->assertTrue($this->db->tableExists('dex_issues'));
        $this->assertTrue($this->db->tableExists('dex_occurrences'));
        $this->assertTrue($this->db->tableExists('dex_requests'));

        // Run down
        $migration = \CodeIgniter\Config\Services::migrations();
        $migration->setNamespace('Dex');
        $migration->regress(0);

        $this->assertFalse($this->db->tableExists('dex_issues'), 'dex_issues should not exist after rollback');
        $this->assertFalse($this->db->tableExists('dex_occurrences'), 'dex_occurrences should not exist after rollback');
        $this->assertFalse($this->db->tableExists('dex_requests'), 'dex_requests should not exist after rollback');

        // Run up again
        $migration->latest();

        $this->assertTrue($this->db->tableExists('dex_issues'), 'dex_issues should exist after re-running migration');
        $this->assertTrue($this->db->tableExists('dex_occurrences'), 'dex_occurrences should exist after re-running migration');
        $this->assertTrue($this->db->tableExists('dex_requests'), 'dex_requests should exist after re-running migration');
    }
}
