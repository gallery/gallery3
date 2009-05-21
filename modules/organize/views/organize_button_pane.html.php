<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gOrganizeEditHandleButtonsLeft">
  <a class="gButtonLink ui-corner-all ui-state-default ui-state-disabled" href="#" ref="edit"
     disabled="1" title="<?= t("Open Drawer") ?>">
    <span class="ui-icon ui-icon-arrowthickstop-1-n"><?= t("Open Drawer") ?></span>
  </a>

  <a class="gButtonLink ui-corner-all ui-state-default ui-state-disabled" href="#" ref="close"
     disabled="1" title="<?= t("Close Drawer") ?>" style="display: none">
    <span class="ui-icon ui-icon-arrowthickstop-1-s"><?= t("Close Drawer") ?></span>
  </a>

  <? if (graphics::can("rotate")): ?>
  <a class="gButtonLink ui-corner-all ui-state-default ui-state-disabled" href="#" ref="rotateCcw"
     disabled="1" title="<?= t("Rotate 90 degrees counter clockwise") ?>">
    <span class="ui-icon ui-icon-rotate-ccw"><?= t("Rotate 90 degrees counter clockwise") ?></span>
  </a>

  <a class="gButtonLink ui-corner-all ui-state-default ui-state-disabled" href="#" ref="rotateCw"
     disabled="1" title="<?= t("Rotate 90 degrees clockwise") ?>">
    <span class="ui-icon ui-icon-rotate-cw"> <?= t("Rotate 90 degrees clockwise") ?></span>
  </a>
  <? endif ?>

  <a class="gButtonLink ui-corner-all ui-state-default ui-state-disabled" href="#" ref="albumCover"
     disabled="1" title="<?= t("Choose this photo as the album cover") ?>">
    <span class="ui-icon ui-icon-star"><?= t("Choose this photo as the album cover") ?></span>
  </a>

  <a class="gButtonLink ui-corner-all ui-state-default ui-state-disabled" href="#" ref="delete"
     disabled="1" title="<?= t("Delete selection") ?>">
    <span class="ui-icon ui-icon-trash"><?= t("Delete selection") ?></span>
  </a>
</div>
<div id="gOrganizeEditHandleButtonsMiddle">
  <a class="gButtonLink ui-corner-all ui-state-default" href="#" ref="submit"
     title="<?= t("Apply Changes") ?>" style="display: none" >
    <span class="ui-icon ui-icon-check"><?= t("Apply Changes") ?></span>
  </a>

  <a class="gButtonLink ui-corner-all ui-state-default" href="#" ref="reset"
     title="<?= t("Reset Form") ?>" style="display: none" >
    <span class="ui-icon ui-icon-closethick"><?= t("Reset Form") ?></span>
  </a>
</div>
<div id="gOrganizeEditHandleButtonsRight">
  <a id="gMicroThumbSelectAll" href="#" ref="select-all" class="gButtonLink ui-corner-all ui-state-default"><?= t("Select all") ?></a>
  <a id="gMicroThumbUnselectAll" href="#" ref="unselect-all" style="display: none" class="gButtonLink ui-corner-all ui-state-default"><?= t("Deselect all") ?></a>
  <a id="gMicroThumbDone" href="#" ref="done" class="gButtonLink ui-corner-all ui-state-default"><?= t("Done") ?></a>
</div>
