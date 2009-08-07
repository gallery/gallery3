<?php defined("SYSPATH") or die("No direct script access.") ?>
<li class="gOrganizeBranch ui-icon-left" ref="<?= $album->id ?>">
  <div id="gOrganizeBranch-<?= $album->id ?>" ref="<?= $album->id ?>"
       class="<?= $selected ? "gBranchSelected" : "" ?> gBranchText">
    <span id="gOrganizeIcon-<?= $album->id ?>" ref="<?= $album->id ?>"
          class="ui-icon <?= $album_icon ?>">
    </span>
    <?= p::clean($album->title) ?>
  </div>
  <ul id="gOrganizeChildren-<?= $album->id ?>"
      class="<?= $album_icon == "ui-icon-plus" ? "gBranchCollapsed" : "" ?>">
    <li style="display:none">&nbsp;</li>
    <? foreach ($children as $child): ?>
      <?= $child ?>
    <? endforeach ?>
  </ul>
</li>
