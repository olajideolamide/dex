<?php
$frames = array_values((array) ($frames ?? []));
$culpritText = '';
if (!empty($culprit['file'] ?? '')) {
    $culpritText = (string) $culprit['file'] . (!empty($culprit['line'] ?? null) ? ':' . (int) $culprit['line'] : '');
}
?>
<section class="dex-card dex-section-anchor" aria-label="Stack trace" data-dex-section="stack">
  <div class="dex-card-header">
    <h2 class="dex-card-title">Stack Trace</h2>
    <div class="d-flex gap-2 align-items-center">

      <div class="dex-frame-toggle">
        <button class="active" type="button" data-frame-filter="all">All</button>
        <button type="button" data-frame-filter="inapp">In-app</button>
      </div>
    </div>
  </div>
  <div class="dex-card-body dex-card-body--flush">
    <?php if ($frames === []) : ?>
      <div class="dex-empty">No stack trace captured for this event.</div>
    <?php else : ?>
        <?php foreach ($frames as $index => $frame) : ?>
            <?php
            $file = (string) ($frame['file'] ?? '');
            $line = (int) ($frame['line'] ?? 0);
            $fn = (string) ($frame['fn'] ?? '');
            $snippet = dex_code_snippet($file, $line, 2);
            $rel = (string) ($snippet['rel'] ?? $file);
            $isInApp = str_starts_with(str_replace('\\', '/', $rel), 'app/') || str_starts_with(str_replace('\\', '/', $rel), 'src/');
            ?>
        <article class="dex-frame <?= $index === 0 ? 'is-open' : '' ?>" data-kind="<?= $isInApp ? 'inapp' : 'vendor' ?>">
          <div class="dex-frame__head">
            <span class="dex-frame__tag <?= $isInApp ? 'dex-frame__tag--inapp' : 'dex-frame__tag--vendor' ?>"><?= $isInApp ? 'in-app' : 'vendor' ?></span>
            <span class="dex-frame__path"><?= esc($rel !== '' ? $rel : 'unknown') ?><?= $line > 0 ? ':' . $line : '' ?></span>
            <span class="dex-frame__fn"><?= esc($fn !== '' ? $fn : 'frame') ?></span>
            <span class="dex-frame__chev"><i class="ti ti-circle-caret-down"></i></span>
          </div>
          <div class="dex-code-block">
            <?php if ($snippet !== null && !empty($snippet['lines'])) : ?>
                <?php foreach ((array) $snippet['lines'] as $snippetLine) : ?>
                    <?php $lineNo = (int) ($snippetLine['no'] ?? 0); ?>
                <div class="dex-code-line <?= $lineNo === (int) ($snippet['line'] ?? 0) ? 'is-highlight' : '' ?>">
                  <span class="dex-code-line__no"><?= str_pad((string) $lineNo, 4, ' ', STR_PAD_LEFT) ?></span>
                  <span class="dex-code-line__src"><?= esc((string) ($snippetLine['text'] ?? '')) ?></span>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
              <div class="dex-code-line">
                <span class="dex-code-line__no">····</span>
                <span class="dex-code-line__src">No code snippet available for this frame.</span>
              </div>
            <?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
        <?php if ($culpritText !== '') : ?>
        <div class="dex-culprit">
          <span class="dex-culprit__label">Culprit:</span>
          <b><?= esc($culpritText) ?></b>
            <?php if (!empty($culprit['fn'] ?? '')) : ?>
            <span style="color:var(--muted)">— <?= esc((string) $culprit['fn']) ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
