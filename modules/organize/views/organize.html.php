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
<div id="doc3" class="yui-t1">
  <div id="hd" ref="<?= url::site("organize/header/__ITEM_ID__") ?>">
    <h2 id="gOrganizeAlbumTitle"><?= $item->title ?></h2>
    <p id="gOrganizeAlbumDescription"><?= $item->description ?></p>
  </div>

  <div id="bd" role="main">
	  <div id="yui-main">
	    <div class="yui-b">
        <div class="yui-ge">
          <div id="gMicroThumbContainer" class="yui-u first"
               ref="<?= url::site("organize/content/__ITEM_ID__?width=__WIDTH__&height=__HEIGHT__&offset=__OFFSET__") ?>">
            <ul id="gMicroThumbGrid">
            </ul>
          </div>
          <div id="gOrganizeEditContainer"  class="yui-u">
            <?= $edit_form ?>
          </div>
        </div>
      </div>
	  </div>
	  <div id="gOrganizeTreeContainer" class="yui-b">
      <?= $album_tree ?>
    </div>
	</div>




  <div id="ft">
    <div class="gProgressBar" style="visibility: hidden"></div>
  </div>
</div>
