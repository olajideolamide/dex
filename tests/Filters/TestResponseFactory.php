<?php

declare(strict_types=1);

namespace Dex\Tests\Filters;

use CodeIgniter\HTTP\ResponseInterface;
use Dex\Adapters\CiResponseFactory;

final class TestResponseFactory extends CiResponseFactory
{
    public function __construct(private readonly ResponseInterface $response)
    {
    }

    public function create(): ResponseInterface
    {
        return $this->response;
    }
}
