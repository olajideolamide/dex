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
use Dex\Models\RequestModel;
use Throwable;

final class RequestRepository
{
    private RequestModel $model;

    public function __construct(?RequestModel $model = null)
    {
        $this->model = $model ?? new RequestModel();
    }

    public function recordRequest(array $request): void
    {
        try {
            $ok = $this->model->insert($request);
        } catch (Throwable $e) {
            throw RepositoryException::writeFailed('request', $e);
        }

        if ($ok === false) {
            throw RepositoryException::writeFailed('request');
        }
    }
}
