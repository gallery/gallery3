<?php defined("SYSPATH") or die("No direct script access.") ?>
<? if ($item->type == "photo"): ?>
<? if (graphics::can("rotate")): ?>
<a class="counter-clockwise" href="<?= url::site("quick/rotate/$item->id/ccw?csrf=" . access::csrf_token()) ?>">
  <span>
    <?= t("Rotate CCW") ?>
  </span>
</a>
<? endif ?>

<a class="edit gDialogLink" href="<?= url::site("quick/form_edit/$item->id") ?>">
  <span>
    <?= t("Edit") ?>
  </span>
</a>

<? if (graphics::can("rotate")): ?>
<a class="clockwise" href="<?= url::site("quick/rotate/$item->id/cw?csrf=" . access::csrf_token()) ?>">
  <span>
    <?= t("Rotate CCW") ?>
  </span>
</a>
<? endif ?>
<? endif ?>
