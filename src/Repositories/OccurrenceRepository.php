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

namespace Dex\Repositories;

use Dex\Domain\Exceptions\RepositoryException;
use Dex\Models\OccurrenceModel;
use Throwable;

final class OccurrenceRepository
{
    private OccurrenceModel $model;

    public function __construct(?OccurrenceModel $model = null)
    {
        $this->model = $model ?? new OccurrenceModel();
    }

    public function recordOccurrence(array $occurrence): void
    {
        try {
            $ok = $this->model->insert($occurrence);
        } catch (Throwable $e) {
            throw RepositoryException::writeFailed('occurrence', $e);
        }

        if ($ok === false) {
            throw RepositoryException::writeFailed('occurrence');
        }
    }
}
