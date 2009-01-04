<?php defined("SYSPATH") or die("No direct script access.") ?>
<? if ($item->type == "photo"): ?>
<div class="rotate-counter-clockwise"
     quickedit_link="<?= url::site("quick/rotate/$item->id/ccw?csrf=" . access::csrf_token()) ?>">
  <span>
    <?= _("Rotate CCW") ?>
  </span>
</div>
<div class="rotate-clockwise"
     quickedit_link="<?= url::site("quick/rotate/$item->id/cw?csrf=" . access::csrf_token()) ?>">
  <span>
    <?= _("Rotate CCW") ?>
  </span>
</div>
<? endif ?>
