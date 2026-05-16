<?php

$eventPager = is_array($eventPager ?? null) ? $eventPager : [];
$eventTotal = (int)($eventPager['total'] ?? 0);
$eventPosition = (int)($eventPager['position'] ?? 0);
$newerOccurrenceId = isset($eventPager['newerOccurrenceId']) ? (int)$eventPager['newerOccurrenceId'] : 0;
$olderOccurrenceId = isset($eventPager['olderOccurrenceId']) ? (int)$eventPager['olderOccurrenceId'] : 0;
$selectedOccurrenceId = (int)($selectedId ?? 0);
$selectedHappenedAt = (string)($selected['happened_at'] ?? '');
$selectedHappenedAtDisplay = dex_format_datetime($selectedHappenedAt !== '' ? $selectedHappenedAt : null);

$requestStatus = is_numeric($status ?? null) ? (int)$status : null;
$statusPill = $requestStatus === null ? '—' : (string)$requestStatus;
$culpritText = '';
if (!empty($culprit['file'] ?? '')) {
    $culpritText = (string)$culprit['file'] . (!empty($culprit['line'] ?? null) ? ':' . (int)$culprit['line'] : '');
}

$requestSummary = [];
if ($rid !== '') {
    $requestSummary[] = ['Request ID', $rid];
}
if ($method !== '') {
    $requestSummary[] = ['Method', $method];
}
if ($path !== '') {
    $requestSummary[] = ['Path', $path];
}
if ($requestStatus !== null) {
    $requestSummary[] = ['Status', (string)$requestStatus];
}
if ($durMs !== null) {
    $requestSummary[] = ['Duration', dex_format_ms((int)$durMs)];
}
if ($dbCnt !== null || $dbMs !== null) {
    $requestSummary[] = ['DB Queries', trim(($dbCnt !== null ? (int)$dbCnt : '—') . ' / ' . ($dbMs !== null ? dex_format_ms((int)$dbMs) : '—'))];
}
if ($memPk !== null) {
    $requestSummary[] = ['Memory', dex_format_bytes((int)$memPk)];
}
if ($controller !== '') {
    $requestSummary[] = ['Controller', $controller];
}
if ($action !== '') {
    $requestSummary[] = ['Action', $action];
}
if ($ip !== '') {
    $requestSummary[] = ['IP', $ip];
}
if ($phpVer !== '') {
    $requestSummary[] = ['PHP', $phpVer];
}
?>
<div class="row g-3" data-dex-issue-event-content data-dex-issue-occurrence="<?= $selectedOccurrenceId ?>">
    <div class="col-lg-8">
        <?php if ($selectedOccurrenceId > 0) : ?>
            <section class="dex-card dex-event-pager mb-3" aria-label="Event navigation">
                <div class="dex-event-pager__main">
                    <div class="dex-event-pager__info">
                        <div class="dex-event-pager__heading">
                            <span class="dex-event-pager__eyebrow">Event</span>
                            <span class="dex-event-pager__id">#<?= $selectedOccurrenceId ?></span>
                            <?php if ($eventTotal > 0 && $eventPosition > 0) : ?>
                                <span class="dex-event-pager__position" aria-label="Event <?= $eventPosition ?> of <?= $eventTotal ?>">
                                    <span class="dex-event-pager__position-num"><?= $eventPosition ?></span>
                                    <span class="dex-event-pager__position-of">of</span>
                                    <span class="dex-event-pager__position-num"><?= $eventTotal ?></span>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($selectedHappenedAt !== '') : ?>
                            <div class="dex-event-pager__meta">
                                <i class="ti ti-clock-hour-4 dex-event-pager__meta-icon" aria-hidden="true"></i>
                                <span class="dex-event-pager__time-rel"><?= esc(dex_time_ago($selectedHappenedAt)) ?></span>
                                <span class="dex-event-pager__sep">·</span>
                                <time class="dex-event-pager__time-abs" datetime="<?= esc($selectedHappenedAt) ?>"><?= esc($selectedHappenedAtDisplay) ?></time>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="dex-event-pager__nav" role="group" aria-label="Event paging controls">
                        <button
                                type="button"
                                class="dex-event-pager__nav-btn"
                                data-dex-issue-paginate="<?= $newerOccurrenceId ?>"
                                aria-label="Newer event"
                            <?= $newerOccurrenceId > 0 ? '' : 'disabled' ?>
                        >
                            <i class="ti ti-chevron-left" aria-hidden="true"></i>
                            <span>Newer</span>
                        </button>
                        <button
                                type="button"
                                class="dex-event-pager__nav-btn"
                                data-dex-issue-paginate="<?= $olderOccurrenceId ?>"
                                aria-label="Older event"
                            <?= $olderOccurrenceId > 0 ? '' : 'disabled' ?>
                        >
                            <span>Older</span>
                            <i class="ti ti-chevron-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <div data-dex-issue-lazy="stack" class="dex-loading">Loading stack trace…</div>

        <div data-dex-issue-lazy="lifecycle" class="dex-loading">Loading lifecycle…</div>
    </div>

    <aside class="col-lg-4">
        <details class="dex-card dex-collapse" aria-label="Issue metadata" open>
            <summary class="dex-card-header">
                <h2 class="dex-card-title">Issue Metadata</h2>
                <div class="dex-collapse__right" aria-hidden="true">
                    <i class="ti ti-chevron-down dex-collapse__chev" aria-hidden="true"></i>
                </div>
            </summary>
            <div class="dex-meta-list">
                <div class="dex-meta-row">
                    <div class="dex-meta-key">First seen</div>
                    <div class="dex-meta-val"><?= esc(dex_format_datetime($issue['first_seen'] ?? null)) ?></div>
                </div>
                <div class="dex-meta-row">
                    <div class="dex-meta-key">Last seen</div>
                    <div class="dex-meta-val"><?= esc(dex_format_datetime($issue['last_seen'] ?? null)) ?></div>
                </div>
                <div class="dex-meta-row">
                    <div class="dex-meta-key">Environment</div>
                    <div class="dex-meta-val"><?= esc($env !== '' ? $env : (string)($issue['environment'] ?? '—')) ?></div>
                </div>
                <div class="dex-meta-row">
                    <div class="dex-meta-key">Release</div>
                    <div class="dex-meta-val">—</div>
                </div>
                <div class="dex-meta-row">
                    <div class="dex-meta-key">Fingerprint</div>
                    <div class="dex-meta-val"><?= esc((string)($issue['fingerprint'] ?? '')) ?></div>
                </div>
                <div class="dex-meta-row">
                    <div class="dex-meta-key">Culprit</div>
                    <div class="dex-meta-val"><?= esc($culpritText !== '' ? $culpritText : '—') ?></div>
                </div>
            </div>
        </details>
        <details class="dex-card dex-collapse" aria-label="Request context" open>
            <summary class="dex-card-header">
                <h2 class="dex-card-title">Request Context</h2>
                <div class="dex-collapse__right">
                    <span class="dex-status-pill"><?= esc($statusPill) ?></span>
                    <i class="ti ti-chevron-down dex-collapse__chev" aria-hidden="true"></i>
                </div>
            </summary>
            <div class="dex-meta-list">
                <?php if ($requestSummary === []) : ?>
                    <div class="dex-empty p-0">No request metadata captured for this event.</div>
                <?php else : ?>
                    <?php foreach ($requestSummary as [$metaKey, $metaValue]) : ?>
                        <div class="dex-meta-row">
                            <div class="dex-meta-key"><?= esc((string)$metaKey) ?></div>
                            <div class="dex-meta-val"><?= esc((string)$metaValue) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </details>

        <details class="dex-card dex-collapse" aria-label="Userland data">
            <summary class="dex-card-header">
                <h2 class="dex-card-title">Userland Data</h2>
                <div class="dex-collapse__right" aria-hidden="true">
                    <i class="ti ti-chevron-down dex-collapse__chev" aria-hidden="true"></i>
                </div>
            </summary>
            <div class="dex-meta-list">
                <?php if (empty($userContextRows ?? [])) : ?>
                    <div class="dex-empty p-0">No userland data captured for this event.</div>
                <?php else : ?>
                    <?php foreach ((array)$userContextRows as $row) : ?>
                        <div class="dex-meta-row">
                            <div class="dex-meta-key"><?= esc((string)($row['k'] ?? '')) ?></div>
                            <div class="dex-meta-val"><?= esc((string)($row['v'] ?? '')) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </details>

        <details class="dex-card dex-collapse" aria-label="HTTP headers">
            <summary class="dex-card-header">
                <h2 class="dex-card-title">HTTP Headers</h2>
                <div class="dex-collapse__right">
                    <span class="dex-frame-count"><?= count((array)($httpHeaderRows ?? [])) ?> captured</span>
                    <i class="ti ti-chevron-down dex-collapse__chev" aria-hidden="true"></i>
                </div>
            </summary>
            <div class="dex-meta-list">
                <?php if (empty($httpHeaderRows ?? [])) : ?>
                    <div class="dex-empty p-0">No sanitized request headers captured for this event.</div>
                <?php else : ?>
                    <?php foreach ((array)$httpHeaderRows as $row) : ?>
                        <div class="dex-meta-row">
                            <div class="dex-meta-key"><?= esc((string)($row['k'] ?? '')) ?></div>
                            <div class="dex-meta-val"><?= esc((string)($row['v'] ?? '')) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </details>

        <details class="dex-card dex-collapse" aria-label="Server data">
            <summary class="dex-card-header">
                <h2 class="dex-card-title">Server Data</h2>
                <div class="dex-collapse__right" aria-hidden="true">
                    <i class="ti ti-chevron-down dex-collapse__chev" aria-hidden="true"></i>
                </div>
            </summary>

            <?php foreach ((array)($serverContextSections ?? []) as $sectionTitle => $sectionRows) : ?>
                <div class="dex-meta-section">
                    <div class="dex-meta-section__title"><?= esc((string)$sectionTitle) ?></div>
                    <div class="dex-meta-list">
                        <?php if ((array)$sectionRows === []) : ?>
                            <div class="dex-empty p-0">Not captured.</div>
                        <?php else : ?>
                            <?php foreach ((array)$sectionRows as $row) : ?>
                                <div class="dex-meta-row">
                                    <div class="dex-meta-key"><?= esc((string)($row['k'] ?? '')) ?></div>
                                    <div class="dex-meta-val"><?= esc((string)($row['v'] ?? '')) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </details>
    </aside>
</div>
