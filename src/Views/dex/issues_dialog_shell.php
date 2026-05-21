<?php

$issueId = (int)($issue['id'] ?? 0);
$statusRaw = strtolower((string)($issue['status'] ?? ''));
$statusLabel = match ($statusRaw) {
    'unhandled' => 'open',
    'regression', 'regressed' => 'regression',
    default => $statusRaw,
};
$statusChipClass = match ($statusLabel) {
    'resolved' => 'dex-chip--resolved',
    'regression' => 'dex-chip--regressed',
    'ignored' => 'dex-chip--ignored',
    default => 'dex-chip--open',
};
?>
<div
        class="dex-v2"
        data-dex-issue-shell
        data-dex-issue-id="<?= $issueId ?>"
        data-dex-issue-occurrence="<?= (int)($selectedId ?? 0) ?>"
        data-dex-issue-dialog-url="<?= esc((string)($dialogUrl ?? '')) ?>"
        data-dex-issue-resolve-url="<?= esc((string)($resolveUrl ?? '')) ?>"
        data-dex-issue-ignore-url="<?= esc((string)($ignoreUrl ?? '')) ?>"
>
    <div class="page-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div class="dex-crumbs">
                <span>Issues</span>
                <span class="sep">/</span>
                <span style="font-weight:700">#<?= $issueId ?></span>
            </div>
            <div class="dex-actions">
                <button
                        type="button"
                        class="btn-action btn-resolve"
                        data-dex-issue-resolve="<?= $issueId ?>"
                        <?= $statusLabel !== 'resolved' ? '' : 'disabled' ?>
                        aria-label="Mark issue as resolved"
                >
                    <i class="ti ti-circle-check" aria-hidden="true"></i>
                    <span>Resolve</span>
                </button>

                <div class="dex-action-menu" data-dex-action-menu>
                    <button
                            type="button"
                            class="btn-action btn-action--icon dex-action-menu__trigger"
                            data-dex-action-menu-trigger
                            aria-haspopup="menu"
                            aria-expanded="false"
                            aria-label="More actions"
                    >
                        <i class="ti ti-dots" aria-hidden="true"></i>
                    </button>
                    <div class="dex-action-menu__panel" role="menu" hidden>
                        <button
                                type="button"
                                class="dex-action-menu__item"
                                role="menuitem"
                                data-dex-issue-ignore="<?= $issueId ?>"
                                <?= $statusLabel !== 'ignored' ? '' : 'disabled' ?>
                        >
                            <i class="ti ti-eye-off" aria-hidden="true"></i>
                            <span>Ignore</span>
                        </button>
                        <?php if (! empty($issue['fingerprint'] ?? '')) : ?>
                            <button
                                    type="button"
                                    class="dex-action-menu__item ms-copy"
                                    role="menuitem"
                                    data-copy="<?= esc((string)($issue['fingerprint'] ?? '')) ?>"
                            >
                                <i class="ti ti-fingerprint" aria-hidden="true"></i>
                                <span>Copy fingerprint</span>
                            </button>
                        <?php endif; ?>
                        <button
                                type="button"
                                class="dex-action-menu__item"
                                role="menuitem"
                                data-dex-issue-close
                        >
                            <i class="ti ti-x" aria-hidden="true"></i>
                            <span>Close</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <section class="dex-card mb-3" aria-label="Issue summary">
            <div class="dex-issue-head">
                <div class="dex-issue-title">
                    <span><?= esc((string)($exc['class'] ?? ($issue['class'] ?? 'UnknownIssue'))) ?></span>
                    <?php if (! empty($exc['code'] ?? null)) :
                        ?><span class="dex-issue-code">code <?= esc((string)$exc['code']) ?></span><?php
                    endif; ?>
                </div>
                <p class="dex-issue-msg">
                    <?= esc((string)($exc['message'] ?? ($selected['message'] ?? ($issue['title'] ?? '')))) ?>
                </p>
                <div class="dex-chips">
                    <span class="dex-chip <?= $statusChipClass ?>" data-dex-issue-status-badge><?= esc(ucfirst($statusLabel)) ?></span>
                    <span class="dex-chip dex-chip--fingerprint ps-0">Fingerprint <code><?= esc((string)($issue['fingerprint'] ?? '')) ?></code></span>
                </div>
            </div>

            <div class="dex-stats">
                <div class="dex-stat">
                    <div class="dex-stat__label">Total events</div>
                    <div class="dex-stat__value"><?= number_format((int)($issue['times_seen'] ?? 0)) ?></div>
                    <div class="dex-stat__sub">across all time</div>
                </div>
                <div class="dex-stat">
                    <div class="dex-stat__label">Last 24h</div>
                    <div class="dex-stat__value" data-dex-issue-occ24h>…</div>
                    <div class="dex-stat__sub">loading recent volume</div>
                </div>
                <div class="dex-stat">
                    <div class="dex-stat__label">Age</div>
                    <div class="dex-stat__value"><?= esc(dex_age($issue['first_seen'] ?? null)) ?></div>
                    <div class="dex-stat__sub">first occurence &rarr; now </div>
                </div>
                <div class="dex-stat">
                    <div class="dex-stat__label">Last seen</div>
                    <div class="dex-stat__value"><?= esc(dex_time_ago($issue['last_seen'] ?? null)) ?></div>
                    <div class="dex-stat__sub"><?= esc(dex_format_datetime($issue['last_seen'] ?? null)) ?></div>
                </div>
            </div>
        </section>

        <div class="row g-3">
            <div class="col-12 mb-3">
                <div data-dex-issue-lazy="metrics" class="dex-loading">Loading event volume…</div>
            </div>
        </div>
        <?= view('Dex\\dex/issues_dialog_event_content', $this->data ?? []) ?>
    </div>
</div>
