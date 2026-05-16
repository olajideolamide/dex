<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use Dex\Support\IpAllowList;
use PHPUnit\Framework\TestCase;

final class IpAllowListTest extends TestCase
{
    public function testEmptyInputsReturnFalse(): void
    {
        $this->assertFalse(IpAllowList::allowed('', '127.0.0.1'));
        $this->assertFalse(IpAllowList::allowed('127.0.0.1', ''));
        $this->assertFalse(IpAllowList::allowed('127.0.0.1', ' , '));
    }

    public function testExactMatchIsCaseInsensitive(): void
    {
        $this->assertTrue(IpAllowList::allowed('127.0.0.1', '127.0.0.1, ::1'));
        $this->assertTrue(IpAllowList::allowed('FE80::1', 'fe80::1'));
    }

    public function testIpv4CidrMatching(): void
    {
        $this->assertTrue(IpAllowList::allowed('192.168.1.10', '192.168.1.0/24'));
        $this->assertFalse(IpAllowList::allowed('192.168.2.10', '192.168.1.0/24'));
    }

    public function testIpv6CidrMatching(): void
    {
        $this->assertTrue(IpAllowList::allowed('2001:db8::1', '2001:db8::/32'));
        $this->assertFalse(IpAllowList::allowed('2001:db9::1', '2001:db8::/32'));
    }
}
