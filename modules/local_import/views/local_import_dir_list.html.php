<?php defined("SYSPATH") or die("No direct script access.") ?>
<span><?= t("Authorized Paths") ?></span>
<ul id="gPathList">
<? foreach ($paths as $id => $path): ?>
  <li class="ui-icon-left">
    <span id="icon_<?= $id?>" class="gRemoveDir ui-icon ui-icon-trash"></span>
    <?= $path ?>
  </li>
<? endforeach ?>
</ul>
<div id="gNoImportPaths" <? if (!empty($paths)): ?>style="display:none"<? endif ?>>
  <span class="gWarning"><?= t("No Authorized upload paths defined") ?></span>
</div>
