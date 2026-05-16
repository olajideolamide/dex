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

namespace Dex\DTO\Issues;

final class IssuesListData
{
    public function __construct(
        public readonly array $issues,
        public readonly ?array $summary,
        public readonly ?array $chart,
        public readonly array $pagination,
        public readonly ?array $filters
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'issues' => $this->issues,
            'pagination' => $this->pagination,
        ];

        if ($this->summary !== null) {
            $data['summary'] = $this->summary;
        }

        if ($this->chart !== null) {
            $data['chart'] = $this->chart;
        }

        if ($this->filters !== null) {
            $data['filters'] = $this->filters;
        }

        return $data;
    }
}
