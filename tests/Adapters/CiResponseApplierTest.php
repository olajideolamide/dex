<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

use CodeIgniter\HTTP\ResponseInterface;
use Dex\Adapters\CiResponseApplier;
use Dex\DTO\ResponseMeta;
use PHPUnit\Framework\TestCase;

final class CiResponseApplierTest extends TestCase
{
    public function testAppliesHeadersFromResponseMeta(): void
    {
        $meta = new ResponseMeta(200, ['X-Request-Id' => 'abc']);
        $meta = $meta->withHeader('X-Other', 'value');

        $calls = [];
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->exactly(2))
            ->method('setHeader')
            ->willReturnCallback(function (string $name, string $value) use (&$calls): void {
                $calls[] = [$name, $value];
            });

        CiResponseApplier::apply($response, $meta);

        $this->assertSame(
            [
                ['x-request-id', 'abc'],
                ['x-other', 'value'],
            ],
            $calls
        );
    }
}
