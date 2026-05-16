<?php
$lifecycleItems = array_values((array) ($lifecycleItems ?? []));
$lifecycleSummaryRows = array_values((array) ($lifecycleSummaryRows ?? []));
$lifecycleHints = array_values((array) ($lifecycleHints ?? []));
?>
<section class="dex-card dex-section-anchor request-lifecycle" aria-label="Request lifecycle" data-dex-section="lifecycle">
  <div class="dex-card-header">
    <h2 class="dex-card-title">
      <i class="ti ti-timeline dex-lifecycle-title-icon" aria-hidden="true"></i>
      Request Lifecycle <small>stored request timeline</small>
    </h2>
    <div class="d-flex gap-2 align-items-center">
      <div class="dex-frame-toggle" data-dex-lifecycle-toggle>
        <button class="active" type="button" data-dex-lifecycle-toggle-action="collapse">Collapsed</button>
        <button type="button" data-dex-lifecycle-toggle-action="expand">Expanded</button>
      </div>
    </div>
  </div>
  <div class="dex-card-body dex-card-body--flush">
      <?php if ($lifecycleItems === []) : ?>
      <div class="dex-empty">No lifecycle captured for this event.</div>
      <?php else : ?>
      <div class="dex-lifecycle-list" data-dex-lifecycle-list>
          <?php foreach ($lifecycleItems as $item) : ?>
                <?php
                $dataRows = array_values((array) ($item['data_rows'] ?? []));
                $depth = (int) ($item['depth'] ?? 0);
                ?>
          <article
            class="dex-lifecycle-item is-collapsed<?= $depth > 0 ? ' dex-lifecycle-item--nested' : '' ?><?= $depth > 0 ? ' dex-lifecycle-item--depth-' . $depth : '' ?>"
            data-dex-lifecycle-item
          >
            <span class="dex-lifecycle-item__marker" aria-hidden="true"></span>
            <div class="dex-lifecycle-item__line">
              <span class="dex-lifecycle-time"><?= esc((string) ($item['time_label'] ?? '')) ?></span>
              <span
                class="badge2 dex-lifecycle-badge <?= esc((string) ($item['type_class'] ?? '')) ?>"
                title="<?= esc((string) ($item['type_tooltip'] ?? '')) ?>"
              >
                <?= esc((string) ($item['type_label'] ?? 'Item')) ?>
              </span>
              <span class="dex-lifecycle-label"><?= esc((string) ($item['label'] ?? '')) ?></span>
              <span class="dex-lifecycle-item__line-end">
                <?php if (($item['duration_label'] ?? '') !== '') : ?>
                  <span class="dex-lifecycle-duration">
                    <i class="ti ti-clock" aria-hidden="true"></i><?= esc((string) $item['duration_label']) ?>
                  </span>
                <?php endif; ?>

                <?php if ($dataRows !== []) : ?>
                  <button
                    class="dex-lifecycle-item__toggle"
                    type="button"
                    aria-expanded="false"
                    aria-label="Expand lifecycle item"
                    data-dex-lifecycle-item-toggle
                  >
                    <i class="ti ti-circle-caret-down" aria-hidden="true"></i>
                  </button>
                <?php endif; ?>
              </span>
            </div>
                <?php if ($dataRows !== []) : ?>
              <div class="dex-kv dex-lifecycle-data" hidden data-dex-lifecycle-item-details>
                    <?php foreach ($dataRows as $row) :
                        if (empty($row['v']) || $row['v'] == "[]") {
                            continue;
                        } ?>
                  <div class="dex-kv-row">
                    <div class="dex-kv-key"><?= esc((string) ($row['k'] ?? '')) ?></div>
                    <div class="dex-kv-val"><?= esc((string) ($row['v'] ?? '')) ?></div>
                  </div>
                    <?php endforeach; ?>
              </div>
                <?php endif; ?>
          </article>
          <?php endforeach; ?>
      </div>
      <?php endif; ?>
  </div>
</section>
