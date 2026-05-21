<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dex\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dex\Adapters\CiResponseFactory;
use Dex\Support\CachedConfigProvider;
use Dex\Support\DexRuntimePolicy;
use Dex\Support\IpAllowList;

class DexUiFilter implements FilterInterface
{
    private CiResponseFactory $responseFactory;

    private CachedConfigProvider $configProvider;
    private DexRuntimePolicy $runtimePolicy;

    public function __construct(
        ?CiResponseFactory $responseFactory = null,
        ?CachedConfigProvider $configProvider = null
    ) {
        $this->responseFactory = $responseFactory ?? new CiResponseFactory();
        $this->configProvider = $configProvider ?? new CachedConfigProvider();
        $this->runtimePolicy = new DexRuntimePolicy($this->configProvider->get());
    }


    public function before(RequestInterface $request, $arguments = null)
    {
        $cfg = $this->configProvider->get();

        if (!$this->runtimePolicy->isEnabled()) {
            return $this->deny($cfg);
        }
        if (ENVIRONMENT === 'production' && ! ($cfg->allowInProduction ?? false)) {
            return $this->deny($cfg);
        }
        if (! ($cfg->uiEnabled ?? false)) {
            return $this->deny($cfg);
        }

        $ip = (string) $request->getIPAddress();

        // Deprecated: prefer uiAllowlist (IPs/CIDR). Kept for backward compatibility.
        if (! empty($cfg->allowedIPs) && ! in_array($ip, (array) $cfg->allowedIPs, true)) {
            return $this->deny($cfg);
        }

        if (! IpAllowList::allowed($ip, (string) ($cfg->uiAllowlist ?? ''))) {
            return $this->deny($cfg);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }

    private function deny(object $cfg)
    {
        if (($cfg->uiStealthDeny ?? true) === true) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->responseFactory->create()->setStatusCode(403, 'Forbidden');
    }
}
