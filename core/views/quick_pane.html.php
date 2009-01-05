<?php defined("SYSPATH") or die("No direct script access.") ?>
<? if ($item->type == "photo"): ?>
<div class="rotate-counter-clockwise"
     href="<?= url::site("quick/rotate/$item->id/ccw?csrf=" . access::csrf_token()) ?>">
  <span>
    <?= _("Rotate CCW") ?>
  </span>
</div>
<div class="edit gDialogLink"
     href="<?= url::site("quick/form_edit/$item->id") ?>">
  <span>
    <?= _("Edit") ?>
  </span>
</div>
<div class="rotate-clockwise"
     href="<?= url::site("quick/rotate/$item->id/cw?csrf=" . access::csrf_token()) ?>">
  <span>
    <?= _("Rotate CCW") ?>
  </span>
</div>
<? endif ?>
