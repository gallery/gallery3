<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var GET_CHILDREN_URL = "<?= url::site("server_add/children?path=__PATH__") ?>";
  var START_URL = "<?= url::site("server_add/start?item_id={$item->id}&csrf=$csrf") ?>";
</script>

<div id="g-server-add">
  <h1 style="display: none;"><?= t("Add Photos to '%title'", array("title" => html::purify($item->title))) ?></h1>

  <p id="g-description"><?= t("Photos will be added to album:") ?></p>
  <ul class="g-breadcrumbs">
    <? $i = 0 ?>
    <? foreach ($item->parents() as $parent): ?>
    <li<? if ($i == 0) print " class=\"g-first\"" ?>> <?= html::purify($parent->title) ?> </li>
    <? $i++ ?>
    <? endforeach ?>
    <li class="g-active"> <?= html::purify($item->title) ?> </li>
  </ul>

  <ul id="g-server-add-tree" class="g-checkbox-tree">
    <?= $tree ?>
  </ul>

  <div id="g-server-add-progress" style="display: none">
    <div class="g-progress-bar"></div>
    <div id="g-status"></div>
  </div>

  <span>
    <button id="g-server-add-add-button" class="ui-state-default ui-state-disabled ui-corner-all"
            disabled="disabled">
      <?= t("Add") ?>
    </button>
    <button id="g-server-add-pause-button" class="ui-state-default ui-corner-all" style="display:none">
      <?= t("Pause") ?>
    </button>
    <button id="g-server-add-continue-button" class="ui-state-default ui-corner-all" style="display:none">
      <?= t("Continue") ?>
    </button>

    <button id="g-server-add-close-button" class="ui-state-default ui-corner-all">
      <?= t("Close") ?>
    </button>
  </span>

  <script type="text/javascript">
    $("#g-server-add").ready(function() {
      $("#g-server-add").gallery_server_add();
    });
  </script>

</div>
