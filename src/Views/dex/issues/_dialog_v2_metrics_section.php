<?php
$hourCounts = array_values((array) ($hourCounts ?? []));
$hourLabels = array_values((array) ($hourLabels ?? []));
$occ24h = (int) ($occ24h ?? array_sum($hourCounts));
?>
<section class="dex-card dex-section-anchor" aria-label="Event volume" data-dex-section="metrics">
  <div class="dex-card-header">
    <h2 class="dex-card-title">Event Volume <small>last 24h</small></h2>
    <span class="dex-frame-count"><?= number_format($occ24h) ?> events</span>
  </div>
  <div class="dex-card-body dex-chart-wrap">
    <canvas data-dex-volume-chart></canvas>
    <div class="dex-x-axis" aria-hidden="true">
      <?php foreach ($hourLabels as $index => $label) : ?>
            <?php if ($index % 2 === 0) : ?>
          <span><?= esc((string) $label) ?>:00</span>
            <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
  <script type="application/json" data-dex-issue-metrics>
    <?= json_encode([
        'hourCounts' => $hourCounts,
        'hourLabels' => $hourLabels,
        'occ24h' => $occ24h,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
  </script>
</section>
