<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use Dex\Support\UserAgentMatcher;
use PHPUnit\Framework\TestCase;

final class UserAgentMatcherTest extends TestCase
{
    public function testContainsBlockedTokenMatchesCaseInsensitiveSubstring(): void
    {
        $result = UserAgentMatcher::containsBlockedToken(
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            ['bingbot', 'googlebot']
        );

        $this->assertTrue($result);
    }

    public function testContainsBlockedTokenReturnsFalseForNonBotUserAgent(): void
    {
        $result = UserAgentMatcher::containsBlockedToken(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/124.0',
            ['googlebot', 'bingbot']
        );

        $this->assertFalse($result);
    }
}
