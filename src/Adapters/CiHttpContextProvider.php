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

use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use Throwable;

class CiHttpContextProvider
{
    public function currentUserAgent(): ?string
    {
        $req = $this->resolveRequest();

        if ($req instanceof IncomingRequest) {
            $userAgent = trim((string) $req->getUserAgent());

            return $userAgent !== '' ? $userAgent : null;
        }

        if (! is_object($req) || ! is_callable([$req, 'getUserAgent'])) {
            return null;
        }

        $userAgent = trim((string) $req->getUserAgent());

        return $userAgent !== '' ? $userAgent : null;
    }

    public function build(bool $includeHeaders, int $maxHeaders, int $maxValueLen): array
    {
        $req = $this->resolveRequest();

        if ($req instanceof RequestInterface) {
            $uri = $req->getUri();
            $out = [
                'method' => $req->getMethod(),
                'path' => (string) $uri->getPath(),
            ];

            try {
                $out['url'] = (string) $uri;
                $out['query'] = (string) $uri->getQuery();
            } catch (Throwable) {
                // ignore
            }
        } elseif (
            is_object($req)
            && is_callable([$req, 'getMethod'])
            && is_callable([$req, 'getUri'])
        ) {
            $uri = $req->getUri();
            $out = [
                'method' => $req->getMethod(),
                'path' => is_object($uri) && is_callable([$uri, 'getPath']) ? (string) $uri->getPath() : null,
            ];

            try {
                $out['url'] = (string) $uri;
                $out['query'] = is_object($uri) && is_callable([$uri, 'getQuery']) ? (string) $uri->getQuery() : null;
            } catch (Throwable) {
                // ignore
            }
        } else {
            return [];
        }

        if (! $includeHeaders) {
            return $out;
        }

        $headers = [];
        $i = 0;

        try {
            foreach ($this->requestHeaders($req) as $name => $_header) {
                if ($maxHeaders > 0 && $i >= $maxHeaders) {
                    break;
                }
                $line = (string) $req->getHeaderLine((string) $name);
                if ($maxValueLen > 0 && mb_strlen($line) > $maxValueLen) {
                    $line = mb_substr($line, 0, $maxValueLen) . '...';
                }
                $headers[(string) $name] = $line;
                $i++;
            }
        } catch (Throwable) {
            // ignore
        }

        $out['headers'] = $headers;

        return $out;
    }

    private function requestHeaders(object $request): array
    {
        if ($request instanceof IncomingRequest || $request instanceof CLIRequest) {
            return $request->getHeaders();
        }

        if (is_callable([$request, 'headers'])) {
            $headers = $request->headers();

            return is_array($headers) ? $headers : [];
        }

        if (is_callable([$request, 'getHeaders'])) {
            $headers = $request->getHeaders();

            return is_array($headers) ? $headers : [];
        }

        return [];
    }

    private function resolveRequest()
    {
        try {
            return service('request');
        } catch (Throwable) {
            return null;
        }
    }
}
