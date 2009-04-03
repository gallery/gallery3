<?php defined("SYSPATH") or die("No direct script access.") ?>

<? if (graphics::can("rotate")): ?>
<a class="gButtonLink ui-corner-all ui-state-default" href="#" ref="rotate_ccw"
  title="<?= t("Rotate 90 degrees counter clockwise") ?>">
  <span class="ui-icon ui-icon-rotate-ccw">
    <?= t("Rotate 90 degrees counter clockwise") ?>
  </span>
</a>

<a class="gButtonLink ui-corner-all ui-state-default" href="#" ref="rotate_cw"
  title="<?= t("Rotate 90 degrees clockwise") ?>">
  <span class="ui-icon ui-icon-rotate-cw">
    <?= t("Rotate 90 degrees clockwise") ?>
  </span>
</a>
<? endif ?>

<a class="gButtonLink ui-corner-all ui-state-default" href="#" ref="delete"
     title="<?= t("Delete selection") ?>">
  <span class="ui-icon ui-icon-trash">
    <?= t("Delete selection") ?>
  </span>
</a>

