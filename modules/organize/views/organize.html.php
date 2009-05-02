<?php defined("SYSPATH") or die("No direct script access.") ?>
<!-- ?= html::script("modules/organize/js/organize.js") ? -->
<script>
  var FATAL_ERROR = "<?= t("Fatal Error") ?>";
  var PAUSE_BUTTON = "<?= t("Pause") ?>";
  var RESUME_BUTTON = "<?= t("Resume") ?>";
  var CANCEL_BUTTON = "<?= t("Cancel") ?>";
  var INVALID_DROP_TARGET = "<div class=\"gError\"><?= t("Drop cancelled as it would result in a recursive move") ?></div>";

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
           ref="<?= url::site("organize/content/__ITEM_ID__?width=__WIDTH__&height=__HEIGHT__&offset=__OFFSET__") ?>">
        <ul id="gMicroThumbGrid"></ul>
      </div>
        <div id="gOrganizeEditDrawer" class="yui-u">
          <div id="gOrganizeEditDrawerPanel" class="yui-gf">
            <div id="gOrganizeFormThumbs" class="yui-u first">
              <div id="gOrganizeFormNoImage">
                <h3><?= t("No Image Selected") ?></h3>
              </div>
              <div id="gOrganizeFormThumb" style="display: none"></div>
              <div id="gOrganizeFormMultipleImages" style="display:none">
                <h3><?= t("Multiple Images Selected") ?></h3>
              </div>
            </div>
            <?= $edit_form ?>
          </div>
          <div id="gOrganizeEditDrawerHandle">
            <div id="gOrganizeEditHandleButtonsLeft">
              <?= $button_pane ?>
            </div>
            <div id="gOrganizeEditHandleButtonsRight">
              <a id="gMicroThumbSelectAll" href="#" ref="select-all" class="gButtonLink ui-corner-all ui-state-default"><?= t("Select all") ?></a>
              <a id="gMicroThumbUnselectAll" href="#" ref="unselect-all" style="display: none" class="gButtonLink ui-corner-all ui-state-default"><?= t("Deselect all") ?></a>
              <a id="gMicroThumbDone" href="#" ref="close" class="gButtonLink ui-corner-all ui-state-default"><?= t("Done") ?></a>
            </div>
          </div>
        </div>
    </div>
  </div>
</div>
