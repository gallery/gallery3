<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= html::script("modules/server_add/js/server_add.js"); ?>
<div id="gServerAdd">
  <h1 style="display: none;"><?= sprintf(t("Add Photos to '%s'"), $album_title) ?></h1>

  <p id="gDescription"><?= t("Photos will be added to album:") ?></p>
  <ul class="gBreadcrumbs">
    <? foreach ($parents as $parent): ?>
    <li><?= $parent->title ?></li>
    <? endforeach ?>
    <li class="active"><?= $album_title ?></li>
  </ul>

  <?= form::open($action, array("method" => "post"), $hidden) ?>
  <div id="gServerAddTree" >
    <?= $tree ?>
  </div>
  <span>
    <?= form::submit(array("id" => "gServerAddButton", "name" => "add", "disabled" => true, "class" => "submit"), t("Add")) ?>
  </span>
  <?= form::close() ?>
  <div id="gProgressBar" style="visibility:hidden;" ></div>
</div>
