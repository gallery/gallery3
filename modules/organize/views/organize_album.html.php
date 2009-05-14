<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <li class="gOrganizeBranch ui-icon-left" ref="<?= $album->id ?>">
    <span id="gOrganizeIcon-<?= $album->id ?>" ref="<?= $album->id ?>"
          class="ui-icon <?= $album_icon ?> <?= $album_icon ? "" : "gBranchEmpty" ?>">
    </span>

    <div id="gOrganizeBranch-<?= $album->id ?>" ref="<?= $album->id ?>"
          class="<?= $selected ? "gBranchSelected" : "" ?> gBranchText">
      <?= $album->title ?>
    </div>
    <div id="gOrganizeChildren-<?= $album->id ?>"
          class="<?= $album_icon == "ui-icon-plus" ? "gBranchCollapsed" : "" ?>">
      <?= $children ?>
    <div>
  </li>
</ul>
