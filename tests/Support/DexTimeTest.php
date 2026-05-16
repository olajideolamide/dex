<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use Dex\Support\DexTime;
use PHPUnit\Framework\TestCase;

final class DexTimeTest extends TestCase
{
    public function testFormatsUtcLabelWhenDisplayTimezoneIsUtc(): void
    {
        $this->assertSame(
            'Jun 6, 2025, 04:13:30 UTC',
            DexTime::formatForDisplay('2025-06-06 04:13:30', (object) ['displayTimezone' => 'UTC'])
        );
    }

    public function testFormatsWithoutUtcSuffixWhenDisplayTimezoneIsNotUtc(): void
    {
        $this->assertSame(
            'Jun 6, 2025, 05:13:30',
            DexTime::formatForDisplay('2025-06-06 04:13:30', (object) ['displayTimezone' => 'Africa/Lagos'])
        );
    }

    public function testFallsBackToUtcForInvalidDisplayTimezone(): void
    {
        $this->assertSame('UTC', DexTime::displayTimezoneName((object) ['displayTimezone' => 'Not/A-Timezone']));
    }
}
