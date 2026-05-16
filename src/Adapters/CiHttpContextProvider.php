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

use Throwable;

class CiHttpContextProvider
{
    public function currentUserAgent(): ?string
    {
        try {
            $req = service('request');
        } catch (Throwable) {
            $req = null;
        }

        if (! $req || ! method_exists($req, 'getUserAgent')) {
            return null;
        }

        $userAgent = trim((string) $req->getUserAgent());

        return $userAgent !== '' ? $userAgent : null;
    }

    public function build(bool $includeHeaders, int $maxHeaders, int $maxValueLen): array
    {
        try {
            $req = service('request');
        } catch (Throwable) {
            $req = null;
        }

        if (! $req) {
            return [];
        }

        $out = [
            'method' => method_exists($req, 'getMethod') ? $req->getMethod() : null,
            'path' => method_exists($req, 'getUri') ? (string) $req->getUri()->getPath() : null,
        ];

        try {
            if (method_exists($req, 'getUri')) {
                $uri = $req->getUri();
                $out['url'] = method_exists($uri, '__toString') ? (string) $uri : null;
                $out['query'] = method_exists($uri, 'getQuery') ? (string) $uri->getQuery() : null;
            }
        } catch (Throwable) {
            // ignore
        }

        if (! $includeHeaders || ! method_exists($req, 'getHeaders')) {
            return $out;
        }

        $headers = [];
        $i = 0;

        try {
            foreach ($req->getHeaders() as $name => $hdr) {
                if ($maxHeaders > 0 && $i >= $maxHeaders) {
                    break;
                }
                $line = method_exists($req, 'getHeaderLine') ? (string) $req->getHeaderLine($name) : (string) $hdr;
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
}
