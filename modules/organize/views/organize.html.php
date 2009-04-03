<?php defined("SYSPATH") or die("No direct script access.") ?>
<!-- ?= html::script("modules/organize/js/organize.js") ? -->
<script>
  var item_id = <?= $item->id ?>;
  var csrf = "<?= $csrf ?>";
  $("#doc3").ready(function() {
    organize_dialog_init();
    $("#gMicroThumbContainer").scroll(function() {
      get_more_data();
    });
  });
</script>
<fieldset style="display: none">
  <legend><?= t("Organize %name", array("name" => $item->title)) ?></legend>
</fieldset>
<div id="doc3" class="yui-t6">
  <div id="hd" ref="<?= url::site("organize/header/__ITEM_ID__") ?>">
    <h2 id="gOrganizeAlbumTitle"><?= $item->title ?></h2>
    <p id="gOrganizeAlbumDescription"><?= $item->description ?></p>
  </div>
  <div id="bd">
    <div id="yui-main">
      <div class="yui-b">
        <div class="yui-gf">
          <div id="gOrganizeTreeContainer" class="yui-u first" style="border: 1px solid">
            <?= $album_tree ?>
          </div>
          <div id="gMicroThumbContainer" class="yui-u" style="border: 1px solid"
               ref="<?= url::site("organize/content/__ITEM_ID__?width=__WIDTH__&height=__HEIGHT__&offset=__OFFSET__") ?>">
            <ul id="gMicroThumbGrid">
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div id="gOrganizeEditContainer"  class="yui-b" style="border: 1px solid">
      <?= $edit_form ?>
    </div>
  </div>
  <div id="ft">
    <div class="gProgressBar" style="visibility: hidden" ></div>
  </div>
</div>
