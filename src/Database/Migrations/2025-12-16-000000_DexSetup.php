<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Dex\Database\Migrations;

use CodeIgniter\Database\Migration;

class DexSetup extends Migration
{
    public function up()
    {
        // dex_issues
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'fingerprint' => ['type' => 'VARCHAR', 'constraint' => 64],
            'level'       => ['type' => 'VARCHAR', 'constraint' => 20],
            'class'       => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 200],
            'latest_path' => ['type' => 'TEXT', 'null' => true],
            'latest_method' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'environment' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'open'],
            'times_seen'  => ['type' => 'INT', 'default' => 1],
            'first_seen'  => ['type' => 'DATETIME'],
            'last_seen'   => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('fingerprint');
        $this->forge->addKey('status');
        $this->forge->addKey('last_seen');
        $this->forge->addKey(['status', 'last_seen'], false, 'idx_dex_issues_status_last_seen');
        $this->forge->createTable('dex_issues', true);

        // dex_occurrences
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'issue_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'request_id'  => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'happened_at' => ['type' => 'DATETIME'],
            'message'     => ['type' => 'TEXT'],
            'context'     => ['type' => 'LONGTEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('issue_id');
        $this->forge->addKey('request_id');
        $this->forge->addKey('happened_at');
        $this->forge->addKey(['issue_id', 'happened_at'], false, 'idx_dex_occurrences_issue_happened_at');
        $this->forge->createTable('dex_occurrences', true);

        // dex_requests
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'request_id'  => ['type' => 'VARCHAR', 'constraint' => 80],
            'method'      => ['type' => 'VARCHAR', 'constraint' => 10],
            'path'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'status_code' => ['type' => 'SMALLINT', 'null' => true],
            'duration_ms' => ['type' => 'INT'],
            'mem_peak'    => ['type' => 'BIGINT'],
            'db_count'    => ['type' => 'INT', 'default' => 0],
            'db_time_ms'  => ['type' => 'INT', 'default' => 0],
            'controller'  => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'action'      => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'snapshot_json' => ['type' => 'LONGTEXT', 'null' => true],
            'lifecycle_json' => ['type' => 'LONGTEXT', 'null' => true],
            'lifecycle_version' => ['type' => 'SMALLINT', 'default' => 2],
            'has_error' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'has_exception' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'slow_request' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'slow_query_count' => ['type' => 'INT', 'default' => 0],
            'slowest_query_ms' => ['type' => 'INT', 'default' => 0],
            'lifecycle_event_count' => ['type' => 'INT', 'default' => 0],
            'manual_span_count' => ['type' => 'INT', 'default' => 0],
            'breadcrumb_count' => ['type' => 'INT', 'default' => 0],
            'created_at'  => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('request_id');
        $this->forge->addUniqueKey('request_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey('slow_request', false, 'idx_dex_requests_slow_request');
        $this->forge->addKey('slow_query_count', false, 'idx_dex_requests_slow_query_count');
        $this->forge->addKey('slowest_query_ms', false, 'idx_dex_requests_slowest_query_ms');
        $this->forge->addKey(['controller', 'action'], false, 'idx_dex_requests_controller_action');
        $this->forge->createTable('dex_requests', true);
    }

    public function down()
    {
        $this->forge->dropTable('dex_requests', true);
        $this->forge->dropTable('dex_occurrences', true);
        $this->forge->dropTable('dex_issues', true);
    }
}
