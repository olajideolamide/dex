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

namespace Dex\Support;

use Dex\DTO\RequestMeta;

final class DexRuntimePolicy
{
    public function __construct(
        private readonly object $config,
    ) {
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->config->enabled ?? false);
    }

    public function shouldRunRequest(RequestMeta $request): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if ($this->isBlockedBotUserAgent($request->userAgent)) {
            return false;
        }

        return !$this->isInternalPath($request->rawPath);
    }

    public function shouldRunContext(?array $context): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (!is_array($context)) {
            return true;
        }

        if ($this->isBlockedBotUserAgent((string) ($context['user_agent'] ?? ''))) {
            return false;
        }

        return !$this->isInternalPath((string) ($context['path'] ?? ''));
    }

    public function shouldRunPath(?string $path): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return !$this->isInternalPath($path ?? '');
    }

    public function shouldRunController(?string $controller): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return !$this->isInternalController((string) ($controller ?? ''));
    }

    public function isBlockedBotUserAgent(?string $userAgent): bool
    {
        $tokens = (array) ($this->config->botUserAgentBlocklist ?? []);

        if ($tokens === []) {
            return false;
        }

        return UserAgentMatcher::containsBlockedToken((string) ($userAgent ?? ''), $tokens);
    }

    public function isInternalPath(string $path): bool
    {
        return PathHelper::isInternalPath($path, $this->config);
    }

    public function isInternalController(string $controller): bool
    {
        return str_starts_with($controller, 'Dex\\');
    }
}
