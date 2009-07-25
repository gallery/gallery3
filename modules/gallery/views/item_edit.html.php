<?php defined("SYSPATH") or die("No direct script access.") ?>
<? if (!empty($script)): ?>
<script>
  <?= implode("\n", $script) ?>
</script>
<? endif ?>
<div id="gEditFormContainer">
  <?= $form ?>
</div>