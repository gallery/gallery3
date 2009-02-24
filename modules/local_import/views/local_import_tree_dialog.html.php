<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
$("#gLocalImport").ready(function() {
  $("#gLocalImport :submit").click(function(event) {
    do_import(this, event);
  });
  $("#gProgressBar").progressbar();
  $("#gLocalImport ul").css("display", "block");
});</script>
<div id="gLocalImport">
  <h1 style="display: none;"><?= sprintf(t("Import Photos to '%s'"), $album_title) ?></h1>

  <p id="gDescription"><?= t("Photos will be imported to album:") ?></p>
  <ul class="gBreadcrumbs">
    <? foreach ($parents as $parent): ?>
      <li><?= $parent->title ?></li>
    <? endforeach ?>
    <li class="active"><?= $album_title ?></li>
  </ul>

  <?= form::open($action, array("method" => "post"), $hidden) ?>
    <div id="gLocalImportTree" >
      <?= $tree ?>
    </div>
    <span>
    <?= form::submit(array("id" => "gImportButton", "name" => "import", "disabled" => true, "class" => "submit"),
                     t("Import")) ?>
    </span>
  <?= form::close() ?> 
  <div id="gProgressBar" ></div>
</div>
