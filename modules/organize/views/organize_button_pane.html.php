<?php defined("SYSPATH") or die("No direct script access.") ?>

<a class="gButtonLink ui-corner-all ui-state-default ui-state-disabled" href="#" ref="edit"
  disabled="1" title="<?= t("Edit Selection") ?>">
  <span class="ui-icon ui-icon-pencil">
    <?= t("Edit Selection") ?>
  </span>
</a>

<? if (graphics::can("rotate")): ?>
<a class="gButtonLink ui-corner-all ui-state-default ui-state-disabled" href="#" ref="rotateCcw"
  disabled="1" title="<?= t("Rotate 90 degrees counter clockwise") ?>">
  <span class="ui-icon ui-icon-rotate-ccw">
    <?= t("Rotate 90 degrees counter clockwise") ?>
  </span>
</a>

<a class="gButtonLink ui-corner-all ui-state-default ui-state-disabled" href="#" ref="rotateCw"
  disabled="1" title="<?= t("Rotate 90 degrees clockwise") ?>">
  <span class="ui-icon ui-icon-rotate-cw">
    <?= t("Rotate 90 degrees clockwise") ?>
  </span>
</a>
<? endif ?>

<a class="gButtonLink ui-corner-all ui-state-default ui-state-disabled" href="#" ref="albumCover"
   disabled="1" title="<?= t("Choose this photo as the album cover") ?>">
  <span class="ui-icon ui-icon-star">
    <?= t("Choose this photo as the album cover") ?>
  </span>
</a>

<a class="gButtonLink ui-corner-all ui-state-default ui-state-disabled" href="#" ref="delete"
   disabled="1" title="<?= t("Delete selection") ?>">
  <span class="ui-icon ui-icon-trash">
    <?= t("Delete selection") ?>
  </span>
</a>

