<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use Dex\Support\Fingerprint;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FingerprintTest extends TestCase
{
    public function testFromMessageNormalizesNumbersAndWhitespace(): void
    {
        $fp1 = Fingerprint::fromMessage('ERROR', 'User 123 failed   login');
        $fp2 = Fingerprint::fromMessage('error', 'User 456 failed login');

        $this->assertSame($fp1, $fp2);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $fp1);
    }

    public function testFromExceptionIsDeterministicForSameException(): void
    {
        $ex = new RuntimeException('Boom 99');

        $fp1 = Fingerprint::fromException($ex);
        $fp2 = Fingerprint::fromException($ex);

        $this->assertSame($fp1, $fp2);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $fp1);
    }

    public function testFromFatalUsesShortPath(): void
    {
        $errA = [
            'message' => 'Fatal error 1',
            'file' => 'C:\\app\\src\\File.php',
            'line' => 10,
        ];
        $errB = [
            'message' => 'Fatal error 1',
            'file' => '/var/www/app/src/File.php',
            'line' => 10,
        ];

        $this->assertSame(Fingerprint::fromFatal($errA), Fingerprint::fromFatal($errB));
    }
}
