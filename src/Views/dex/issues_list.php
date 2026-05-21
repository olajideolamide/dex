<?= $this->extend('Dex\\dex/layout') ?>

<?= $this->section('content') ?>

<div class="dex-issues-v2">
    <div class="page-wrap">
        <div class="mb-4">
            <h1 class="page-title">Issues</h1>
            <p class="page-subtitle">
                <?php if (($isDexRunningInProduction ?? false) === true) : ?>
                <span class="badge bg-orange"></span> Production mode - <abbr class="text-muted" title="Protect DEX in production: require login or restrict IPs. Learn more in the docs.">Secure DEX</abbr>
                <?php else : ?>
                <span class="badge bg-green"></span> <?= esc(ucfirst((string)($ciEnvironment ?? 'unknown'))) ?> mode
                <?php endif; ?>
            </p>
        </div>
        

        <div class="d-flex gap-3 mb-4" id="statCards">
            <div class="stat-card">
                <div class="stat-label">Total Issues</div>
                <div class="stat-value" id="summary-total"><?= esc((string)($summary['totalIssues'] ?? 0)) ?></div>
                <div class="stat-sub">across all statuses</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Open</div>
                <div class="stat-value" id="summary-open"><?= esc((string)($summary['openIssues'] ?? 0)) ?></div>
                <div class="stat-sub">need attention</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Regressed</div>
                <div class="stat-value" id="summary-regressed"><?= esc((string)($summary['regressedIssues'] ?? 0)) ?></div>
                <div class="stat-sub">previously resolved</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Events (24h)</div>
                <div class="stat-value" id="summary-events"><?= esc((string)($summary['events24h'] ?? 0)) ?></div>
                <div class="stat-sub"><span class="stat-trend" id="summary-trend"><?php $trendPct = $summary['eventsTrendPct'] ?? null; ?>
                        <?= $trendPct === null ? 'No baseline' : (($trendPct >= 0 ? '+' : '-') . abs((int)round($trendPct)) . '%') ?></span> vs prev 24h
                </div>
            </div>
        </div>

        <div class="chart-card mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="chart-title">Event Volume</span>
                <span class="chart-subtitle">events / hour (24h)</span>
                <div class="ms-auto d-flex align-items-center gap-1 dex-issues-v2__chart-legend">
                    <span class="dex-issues-v2__chart-legend-line"></span>
                    All events
                </div>
            </div>
            <canvas id="volumeChart"></canvas>
            <div class="chart-labels" id="chartLabels"></div>
        </div>

        <div class="filter-bar">
            <div class="filter-tabs" id="filterTabs">
                <button class="filter-tab<?= (($filters['status'] ?? 'all') === 'all') ? ' active' : '' ?>" data-filter="all">All <span class="filter-count" id="count-all"><?= esc((string)($summary['totalIssues'] ?? 0)) ?></span></button>
                <button class="filter-tab<?= (($filters['status'] ?? '') === 'open') ? ' active' : '' ?>" data-filter="open"><span class="tab-dot open"></span>Open <span class="filter-count" id="count-open"><?= esc((string)($summary['openIssues'] ?? 0)) ?></span></button>
                <button class="filter-tab<?= (($filters['status'] ?? '') === 'regressed') ? ' active' : '' ?>" data-filter="regressed"><span class="tab-dot regressed"></span>Regressed <span class="filter-count" id="count-regressed"><?= esc((string)($summary['regressedIssues'] ?? 0)) ?></span></button>
                <button class="filter-tab<?= (($filters['status'] ?? '') === 'resolved') ? ' active' : '' ?>" data-filter="resolved"><span class="tab-dot resolved"></span>Resolved <span class="filter-count" id="count-resolved"><?= esc((string)($summary['resolvedIssues'] ?? 0)) ?></span></button>
                <button class="filter-tab<?= (($filters['status'] ?? '') === 'ignored') ? ' active' : '' ?>" data-filter="ignored"><span class="tab-dot ignored"></span>Ignored <span class="filter-count" id="count-ignored"><?= esc((string)($summary['ignoredIssues'] ?? 0)) ?></span></button>
            </div>
            <div class="search-wrap">
                <span class="search-icon">&#8981;</span>
                <input class="search-input" id="searchInput" type="text" value="<?= esc((string)($filters['q'] ?? '')) ?>" placeholder="Search class, path or message..."/>
            </div>
        </div>

        <div class="issues-table-wrap">
            <table class="issues-table">
                <thead>
                <tr>
                    <th>Issue</th>
                    <th class="text-end">Events</th>
                    <th class="text-end">24h</th>
                    <th>Trend</th>
                    <th class="text-end">Last Seen</th>
                    <th class="text-end dex-issues-v2__th-age">Age</th>
                    <th class="dex-issues-v2__th-chevron"></th>
                </tr>
                </thead>
                <tbody id="issuesTbody"></tbody>
            </table>
        </div>

        <div class="table-footer">
            <span id="showingCount">Showing <?= esc((string)($pagination['from'] ?? 0)) ?>-<?= esc((string)($pagination['to'] ?? 0)) ?> of <?= esc((string)($pagination['total'] ?? 0)) ?> issues</span>
            <div class="footer-actions">
                <span id="pageInfo">Page <?= esc((string)($pagination['page'] ?? 1)) ?> of <?= esc((string)($pagination['pages'] ?? 1)) ?></span>
                <button class="btn btn-sm btn-outline-secondary" id="prevPage"<?= empty($pagination['hasPrev']) ? ' disabled' : '' ?>><i class="ti ti-chevron-left"></i></button>
                <button class="btn btn-sm btn-outline-secondary" id="nextPage"<?= empty($pagination['hasNext']) ? ' disabled' : '' ?>><i class="ti ti-chevron-right"></i></button>
            </div>
        </div>
    </div>

    <div class="dex-issue-overlay" id="dexIssueOverlay" hidden aria-hidden="true">
        <div class="dex-issue-overlay__backdrop" data-dex-issue-close></div>
        <div class="dex-issue-overlay__panel" role="dialog" aria-modal="true" aria-label="Issue details" tabindex="-1">
            <div class="dex-issue-overlay__content" id="dexIssueOverlayContent">
                <div class="dex-issue-overlay__loading">Loading issue…</div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
