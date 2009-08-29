<?php defined("SYSPATH") or die("No direct script access.") ?>
<!-- ?= html::script("modules/organize/js/organize.js") ? -->
<script>
  var FATAL_ERROR = "<?= t("Fatal Error")->for_js() ?>";
  var PAUSE_BUTTON = "<?= t("Pause")->for_js() ?>";
  var RESUME_BUTTON = "<?= t("Resume"->for_js()) ?>";
  var CANCEL_BUTTON = "<?= t("Cancel")->for_js() ?>";
  var INVALID_DROP_TARGET = "<div class=\"gError\"><?= t("Drop cancelled as it would result in a recursive move")->for_js() ?></div>";
var CONFIRM_DELETE = "<?= t("Do you really want to delete the selected albums and/or photos")->for_js() ?>"
  var item_id = <?= $item->id ?>;

  var csrf = <?= json_encode($csrf) ?>;
  var rearrangeUrl = "<?= url::site("__URI__/__ITEM_ID____TASK_ID__?csrf=$csrf")->for_js() ?>";
  $("#doc3").ready(function() {
    organize_dialog_init();
  });
</script>
<fieldset style="display: none">
  <legend><?= t("Organize %name", array("name" => SafeString::purify($item->title))) ?></legend>
</fieldset>
<div id="doc3" class="yui-t7">
  <div id="bd">
    <div class="yui-gf">
      <div class="yui-u first">
        <h3><?= t("Albums") ?></h3>
      </div>
      <div id="gMessage" class="yui-u">
        <div class="gInfo"><?= t("Select one or more items to edit; drag and drop items to re-order or move between albums") ?></div>
      </div>
    </div>
    <div class="yui-gf">
      <div id="gOrganizeTreeContainer" class="yui-u first">
        <?= $album_tree ?>
      </div>
      <div id="gMicroThumbPanel" class="yui-u"
           ref="<?= url::site("organize/content/__ITEM_ID__?width=__WIDTH__&amp;height=__HEIGHT__&amp;offset=__OFFSET__") ?>">
        <ul id="gMicroThumbGrid"></ul>
      </div>
      <div id="gOrganizeEditDrawer" class="yui-u">
        <div id="gOrganizeEditDrawerPanel" class="yui-gf">
          <div id="gOrganizeFormThumbs" class="yui-u first">
            <ul id="gOrganizeFormThumbStack" />
          </div>
          <div id="gOrganizeEditForm">
          </div>
        </div>
        <div id="gOrganizeEditDrawerHandle">
          <?= $button_pane ?>
        </div>
      </div>
    </div>
  </div>
</div>
