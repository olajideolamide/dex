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

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use Dex\DTO\RequestMeta;

final class CiRequestMetaFactory
{
    public static function fromRequest(RequestInterface $request, object $config): RequestMeta
    {
        $method = (string) $request->getMethod();
        $path = (string) $request->getUri()->getPath();
        $ip = trim((string) $request->getIPAddress());
        $ip = $ip !== '' ? $ip : null;
        $ua = $request instanceof IncomingRequest
            ? trim((string) $request->getUserAgent())
            : (is_callable([$request, 'getUserAgent']) ? trim((string) $request->getUserAgent()) : null);
        $ua = $ua !== '' ? $ua : null;

        $headerName = (string) ($config->requestIdHeader ?? 'X-Request-Id');
        $incoming = trim((string) $request->getHeaderLine($headerName));
        $incoming = $incoming !== '' ? $incoming : null;

        return new RequestMeta($method, $path, $ip, $ua, $incoming);
    }
}
