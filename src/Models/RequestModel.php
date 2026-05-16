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

namespace Dex\Models;

use CodeIgniter\Model;

class RequestModel extends Model
{
    protected $table         = 'dex_requests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'request_id', 'method', 'path', 'status_code', 'duration_ms', 'mem_peak',
        'db_count', 'db_time_ms', 'controller', 'action', 'created_at',
        'lifecycle_json', 'lifecycle_version', 'has_error', 'has_exception',
        'slow_request', 'slow_query_count', 'slowest_query_ms',
        'lifecycle_event_count', 'manual_span_count', 'breadcrumb_count',
        'snapshot_json',
    ];
}
