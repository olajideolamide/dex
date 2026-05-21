<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use CodeIgniter\Test\DatabaseTestTrait;

abstract class DexDatabaseTestCase extends DexTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'Dex';
}
