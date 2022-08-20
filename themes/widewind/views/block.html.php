<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php if ($anchor): ?>
<a name="<?= $anchor ?>"></a>
<?php endif ?>
<div id="<?= $css_id ?>" class="g-block">
  <h2><?= $title ?></h2>
  <div class="g-block-content">
    <?= $content ?>
  </div>
</div>
