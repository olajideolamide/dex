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

namespace Dex\DTO;

final class ResponseMeta
{
    /** @var array<string,string> */
    private array $headers;

    public function __construct(
        public readonly int $statusCode,
        array $headers = []
    ) {
        $this->headers = self::normalizeHeaders($headers);
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[self::normalizeHeaderName($name)] = $value;
        return $clone;
    }

    /** @return array<string,string> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function headerLine(string $name): ?string
    {
        $key = self::normalizeHeaderName($name);
        return $this->headers[$key] ?? null;
    }

    /** @return array<string,string> */
    private static function normalizeHeaders(array $headers): array
    {
        $out = [];
        foreach ($headers as $k => $v) {
            $key = self::normalizeHeaderName((string) $k);
            $out[$key] = (string) $v;
        }
        return $out;
    }

    private static function normalizeHeaderName(string $name): string
    {
        return strtolower(trim($name));
    }
}
