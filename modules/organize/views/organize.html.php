<?php defined("SYSPATH") or die("No direct script access.") ?>
<!-- ?= html::script("modules/organize/js/organize.js") ? -->
<script>
  var FATAL_ERROR = "<?= t("Fatal Error") ?>";
  var PAUSE_BUTTON = "<?= t("Pause") ?>";
  var RESUME_BUTTON = "<?= t("Resume") ?>";
  var CANCEL_BUTTON = "<?= t("Cancel") ?>";
  var OPERATION_RUNNING = "<?= t("Operation in Progress") ?>";
  var INVALID_DROP_TARGET = "<div class=\"gError\"><?= t("Drop cancelled as it would result in a recursive move") ?></div>";
  var MOVE_PAUSED = "<div class=\"gWarning\"><?= t("The move operation was paused") ?></div>";
  var MOVE_RESUMED = "<div class=\"gWarning\"><?= t("The move operation was resumed") ?></div>";
  var REARRANGE_PAUSED = "<div class=\"gWarning\"><?= t("The rearrange operation was paused") ?></div>";
  var REARRANGE_RESUMED = "<div class=\"gWarning\"><?= t("The rearrange operation was resumed") ?></div>";

  var item_id = <?= $item->id ?>;

  var csrf = "<?= $csrf ?>";
  var rearrangeUrl = "<?= url::site("__URI__/__ITEM_ID____TASK_ID__?csrf=$csrf") ?>";
  $("#doc3").ready(function() {
    organize_dialog_init();
  });
</script>
<fieldset style="display: none">
  <legend><?= t("Organize %name", array("name" => $item->title)) ?></legend>
</fieldset>
<div id="doc3" class="yui-t1">
  <div id="bd" role="main">
    <div id="yui-main">
      <div class="yui-b">
        <a id="gMicroThumbSelectAll" href="#"><?= t("select all") ?></a>
        <a id="gMicroThumbUnselectAll" href="#" style="display: none"><?= t("deselect all") ?></a>
        <div id="gMicroThumbPanel" class="yui-u first"
             ref="<?= url::site("organize/content/__ITEM_ID__?width=__WIDTH__&height=__HEIGHT__&offset=__OFFSET__") ?>">
            <ul id="gMicroThumbGrid">
            </ul>
        </div>
          <!-- div id="gOrganizeEditContainer"  class="yui-u">
            < ?= $edit_form ? >
          </div -->
      </div>
    </div>
    <div id="gOrganizeTreeContainer" class="yui-b">
      <h3><?= t("Albums") ?></h3>
      <?= $album_tree ?>
    </div>
  </div>
  <div id="ft">
    <div id="gOrganizeStatus"></div>
  </div>
</div>
