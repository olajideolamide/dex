<?php

$dialogTab = (string) ($dialogTab ?? '');
$viewData = $this->data ?? [];
?>

<?php if ($dialogTab === 'metrics') : ?>
    <?= view('Dex\\dex/issues/_dialog_v2_metrics_section', $viewData) ?>
<?php elseif ($dialogTab === 'stack') : ?>
    <?= view('Dex\\dex/issues/_dialog_v2_stack_section', $viewData) ?>
<?php elseif ($dialogTab === 'lifecycle') : ?>
    <?= view('Dex\\dex/issues/_dialog_v2_lifecycle_section', $viewData) ?>
<?php elseif ($dialogTab === 'http') : ?>
    <?= view('Dex\\dex/issues/_http_tab', $viewData) ?>
<?php elseif ($dialogTab === 'tags') : ?>
    <?= view('Dex\\dex/issues/_tags_tab', $viewData) ?>
<?php elseif ($dialogTab === 'raw') : ?>
    <?= view('Dex\\dex/issues/_raw_tab', $viewData) ?>
<?php endif;
