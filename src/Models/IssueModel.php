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

class IssueModel extends Model
{
    protected $table         = 'dex_issues';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'fingerprint',
        'level',
        'class',
        'title',
        'status',
        'latest_path',
        'latest_method',
        'environment',
        'times_seen',
        'first_seen',
        'last_seen',
    ];
}
