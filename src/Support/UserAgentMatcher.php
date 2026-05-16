<?php

declare(strict_types=1);

namespace Dex\Support;

final class UserAgentMatcher
{
    public static function containsBlockedToken(?string $userAgent, array $blockedTokens): bool
    {
        if ($userAgent === null) {
            return false;
        }

        $normalizedUserAgent = mb_strtolower(trim($userAgent));
        if ($normalizedUserAgent === '') {
            return false;
        }

        foreach ($blockedTokens as $blockedToken) {
            $normalizedBlockedToken = mb_strtolower(trim((string) $blockedToken));
            if ($normalizedBlockedToken === '') {
                continue;
            }

            if (str_contains($normalizedUserAgent, $normalizedBlockedToken)) {
                return true;
            }
        }

        return false;
    }
}
