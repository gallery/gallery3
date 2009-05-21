<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= $theme->footer() ?>
<? if ($footer_text = module::get_var("core", "footer_text")): ?>
<?= $footer_text ?>
<? else: ?>
<ul id="gCredits">
  <?= $theme->credits() ?>
</ul>
<? endif ?>
