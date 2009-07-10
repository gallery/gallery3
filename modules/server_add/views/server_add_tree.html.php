<?php defined("SYSPATH") or die("No direct script access.") ?>
<? foreach ($files as $file): ?>
<? $id = substr(md5($file), 10) ?>
<li id="file_<?= $id ?>" class="<?= is_file($file) ? "gFile" : "gDirectory gCollapsed ui-icon-left" ?>">
  <? if (is_dir($file)): ?>
  <span onclick="open_close_branch('<?=$file?>', '<?=$id?>')" class="ui-icon ui-icon-plus"></span>
  <? endif ?>
  <label>
    <?= form::checkbox("path[]", $file, false, "onclick=click_node(this)") ?>
    <?= p::clean(basename($file)) ?>
  </label>
  <? if (is_dir($file)): ?>
  <ul id="tree_<?= $id ?>" style="display: none"></ul>
  <? endif ?>
</li>
<? endforeach ?>
<? if (!$files): ?>
<li class="gFile"> <i> <?= t("empty") ?> </i> </li>
<? endif ?>
