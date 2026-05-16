<!-- Tags -->
<div class="tab-pane fade" id="ms-tab-tags" role="tabpanel">
  <?php if (empty($tags)) : ?>
    <div class="text-muted">No tags captured.</div>
  <?php else : ?>
      <?php
        $tagRows = [];
        foreach ($tags as $k => $v) {
            $tagRows[] = ['k' => (string)$k, 'v' => (string)$v, 'mono' => true, 'copy' => (string)$v];
        }
        ?>
    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="card card-sm">
          <div class="card-header"><h3 class="card-title mb-0">Tags</h3></div>
          <div class="card-body">
            <?= dex_kv_table($tagRows) ?>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="card card-sm">
          <div class="card-header"><h3 class="card-title mb-0">Client</h3></div>
          <div class="card-body">
            <?= dex_kv_table([
              ['k' => 'User agent', 'v' => $ua, 'mono' => true, 'copy' => $ua],
              ['k' => 'Browser', 'v' => $uaParts['browser'] ?? null],
              ['k' => 'OS', 'v' => $uaParts['os'] ?? null],
              ['k' => 'Device', 'v' => $uaParts['device'] ?? null],
            ]) ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

