<?php defined("SYSPATH") or die("No direct script access.") ?>
<? if ($item->type == "photo"): ?>
<? if (graphics::can("rotate")): ?>
<div class="rotate-counter-clockwise"
     href="<?= url::site("quick/rotate/$item->id/ccw?csrf=" . access::csrf_token()) ?>">
  <span>
    <?= t("Rotate CCW") ?>
  </span>
</div>
<? endif ?>

<div class="edit gDialogLink"
     href="<?= url::site("quick/form_edit/$item->id") ?>">
  <span>
    <?= t("Edit") ?>
  </span>
</div>

<? if (graphics::can("rotate")): ?>
<div class="rotate-clockwise"
     href="<?= url::site("quick/rotate/$item->id/cw?csrf=" . access::csrf_token()) ?>">
  <span>
    <?= t("Rotate CCW") ?>
  </span>
</div>
<? endif ?>
<? endif ?>
