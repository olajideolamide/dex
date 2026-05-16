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

class OccurrenceModel extends Model
{
    protected $table         = 'dex_occurrences';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'issue_id', 'request_id', 'happened_at', 'message', 'context'
    ];
}
