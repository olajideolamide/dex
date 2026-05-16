<!-- HTTP -->
<div class="tab-pane fade" id="ms-tab-http" role="tabpanel">

  <?php if (empty($http) && empty($reqSnap)) : ?>
    <div class="text-muted">No HTTP context captured for this event.</div>
  <?php else : ?>
    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="card card-sm">
          <div class="card-header">
            <h3 class="card-title mb-0">Request</h3>
          </div>
          <div class="card-body">
            <?= dex_kv_table([
              ['k' => 'Method', 'v' => $http['method'] ?? $method, 'mono' => true],
              ['k' => 'Path', 'v' => $http['path'] ?? $path, 'mono' => true],
              ['k' => 'URL', 'v' => $fullUrl, 'mono' => true, 'copy' => $fullUrl],
              ['k' => 'Query', 'v' => $query, 'mono' => true],
            ]) ?>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="card card-sm">
          <div class="card-header">
            <h3 class="card-title mb-0">Headers</h3>
            <div class="card-subtitle"><?= $hasHttpHeaders ? 'Captured on error (if enabled)' : 'Not captured' ?></div>
          </div>
          <div class="card-body">
            <?php if ($hasHttpHeaders) :
                ?>
                <?= dex_kv_table((array) ($httpHeaderRows ?? [])) ?>
                <?php
            else :
                ?>
              <div class="text-muted">
                No sanitized request headers were captured for this event.
              </div>
                <?php
            endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>

