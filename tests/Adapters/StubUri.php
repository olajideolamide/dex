<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

use CodeIgniter\HTTP\URI;

final class StubUri extends URI
{
    public function __construct(string $path)
    {
        parent::__construct($path);
    }
}
