<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var GET_CHILDREN_URL = "<?= url::site("server_add/children?path=__PATH__") ?>";
  var START_URL = "<?= url::site("server_add/start?item_id={$item->id}&csrf=$csrf") ?>";
</script>

<div id="gServerAdd">
  <h1 style="display: none;"><?= t("Add Photos to '%title'", array("title" => p::clean($item->title))) ?></h1>

  <p id="gDescription"><?= t("Photos will be added to album:") ?></p>
  <ul class="gBreadcrumbs">
    <? foreach ($item->parents() as $parent): ?>
    <li>
      <?= p::clean($parent->title) ?>
    </li>
    <? endforeach ?>
    <li class="active">
      <?= p::clean($item->title) ?>
    </li>
  </ul>

  <?= form::open(url::abs_site("server_add/start/$item->id"), array("method" => "post")) ?>
  <?= access::csrf_form_field(); ?>
  <ul id="gServerAddTree" class="gCheckboxTree">
    <?= $tree ?>
  </ul>

  <div class="gProgressBar" style="display: none"></div>

  <span>
    <input id="gServerAddAddButton" class="submit ui-state-disabled" disabled="disabled"
           type="submit" value="<?= t("Add") ?>">
  </span>
  <?= form::close() ?>
  <script type="text/javascript">
    $("#gServerAddAddButton").ready(function() {
      $("#gServerAddAddButton").click(function(event) {
         event.preventDefault();
         $("#gServerAdd .gProgressBar").
           progressbar().
           progressbar("value", 0).
           slideDown("fast", function() { start_add() });
      });
    });
  </script>
</div>
