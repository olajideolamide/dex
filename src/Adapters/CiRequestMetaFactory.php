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

namespace Dex\Adapters;

use CodeIgniter\HTTP\RequestInterface;
use Dex\DTO\RequestMeta;

final class CiRequestMetaFactory
{
    public static function fromRequest(RequestInterface $request, object $config): RequestMeta
    {
        $method = method_exists($request, 'getMethod') ? (string) $request->getMethod() : 'GET';
        $path = method_exists($request, 'getUri') ? (string) $request->getUri()->getPath() : '/';
        $ip = method_exists($request, 'getIPAddress') ? (string) $request->getIPAddress() : null;
        $ua = method_exists($request, 'getUserAgent') ? (string) $request->getUserAgent() : null;

        $headerName = (string) ($config->requestIdHeader ?? 'X-Request-Id');
        $incoming = method_exists($request, 'getHeaderLine') ? trim((string) $request->getHeaderLine($headerName)) : '';
        $incoming = $incoming !== '' ? $incoming : null;

        return new RequestMeta($method, $path, $ip, $ua, $incoming);
    }
}
