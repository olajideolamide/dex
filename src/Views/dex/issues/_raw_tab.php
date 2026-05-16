<!-- Raw -->
<div class="tab-pane fade" id="ms-tab-raw" role="tabpanel">
  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card card-sm">
        <div class="card-header">
          <h3 class="card-title mb-0">Occurrence context</h3>
          <div class="card-subtitle">Raw JSON (scrubbed)</div>
        </div>
        <div class="card-body">
          <?= dex_code_block(json_encode($ctx, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), json_encode($ctx)) ?>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card card-sm">
        <div class="card-header">
          <h3 class="card-title mb-0">Request row</h3>
          <div class="card-subtitle">Recorded request metadata</div>
        </div>
        <div class="card-body">
          <?php if (!$hasReq) : ?>
            <div class="text-muted">No request record for this event.</div>
          <?php else : ?>
              <?= dex_code_block(json_encode($requestRow, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), json_encode($requestRow)) ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
