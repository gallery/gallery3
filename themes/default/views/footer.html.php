<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= $theme->footer() ?>
<? if ($footer_text = module::get_var("gallery", "footer_text")): ?>
<?= $footer_text ?>
<? endif ?>

<? if (module::get_var("gallery", "show_credits")): ?>
<ul id="gCredits">
  <?= $theme->credits() ?>
</ul>
<? endif ?>
