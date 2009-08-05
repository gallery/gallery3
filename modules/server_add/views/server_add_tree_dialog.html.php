<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var GET_CHILDREN_URL = "<?= url::site("server_add/children?path=__PATH__") ?>";
  var START_URL = "<?= url::site("server_add/start?item_id={$item->id}&csrf=$csrf") ?>";
</script>

<div id="gServerAdd">
  <h1 style="display: none;"><?= t("Add Photos to '%title'", array("title" => p::purify($item->title))) ?></h1>

  <p id="gDescription"><?= t("Photos will be added to album:") ?></p>
  <ul class="gBreadcrumbs">
    <? foreach ($item->parents() as $parent): ?>
    <li>
      <?= p::purify($parent->title) ?>
    </li>
    <? endforeach ?>
    <li class="active">
      <?= p::purify($item->title) ?>
    </li>
  </ul>

  <ul id="gServerAddTree" class="gCheckboxTree">
    <?= $tree ?>
  </ul>

  <div id="gProgress" style="display: none">
    <div class="gProgressBar"></div>
    <div id="gStatus"></div>
  </div>

  <span>
    <button id="gServerAddAddButton" class="ui-state-default ui-state-disabled ui-corner-all"
            disabled="disabled">
      <?= t("Add") ?>
    </button>

    <button class="ui-state-default ui-corner-all" onclick="closeDialog(); window.location.reload();">
      <?= t("Close") ?>
    </button>
  </span>

  <script type="text/javascript">
    $("#gServerAddAddButton").ready(function() {
      $("#gServerAddAddButton").click(function(event) {
         event.preventDefault();
         $("#gServerAdd .gProgressBar").
           progressbar().
           progressbar("value", 0);
         $("#gProgress").slideDown("fast", function() { start_add() });
      });
    });
  </script>

</div>
