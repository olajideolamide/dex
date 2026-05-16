<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use CodeIgniter\HTTP\URI;

final class FakeUri extends URI
{
    public function __construct(string $uri)
    {
        parent::__construct($uri);
    }
}
