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

namespace Dex\Contracts;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Dex\DTO\RequestMeta;
use Dex\DTO\ResponseMeta;
use Throwable;

interface DexInterface
{
    public function bootstrap(): void;

    public function startRequest(RequestInterface $request, ResponseInterface $response): void;

    public function finishRequest(ResponseInterface $response): void;

    public function start(RequestMeta $request): void;

    public function shouldRunRequest(RequestMeta $request): bool;

    public function finish(ResponseMeta $response): ResponseMeta;

    public function tagController(): void;

    public function trackDbQuery(object $query): void;

    public function markLifecycleEvent(string $name, string $label, array $metadata = []): void;

    public function captureException(Throwable $e, bool $unhandled = false): void;

    public function addBreadcrumb(string $category, string $message, array $data = [], string $level = 'info'): void;

    public function startSpan(string $op, ?string $description = null, array $tags = []): ?string;

    public function finishSpan(?string $id): void;
}
