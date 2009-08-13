<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gOrganize" class="gDialogPanel">
  <h1 style="display:none"><?= t("Organize %name", array("name" => p::purify($title))) ?></h1>
  <div id="bd">
    <div class="yui-gf">
      <div class="yui-u first">
        <h3><?= t("Albums") ?></h3>
      </div>
      <div id="gMessage" class="yui-u">
        <div class="gInfo"><?= t("Select one or more items to edit; drag and drop items to re-order or move between albums") ?></div>
      </div>
    </div>
    <div id= "gOrganizeContentPane" class="yui-gf">
      <div id="gOrganizeTreeContainer" class="yui-u first">
        <ul id="gOrganizeAlbumTree">
          <?= $album_tree ?>
        </ul>
      </div>
      <div id="gOrganizeDetail" class="yui-u">
        <div id="gMicroThumbPanel"
           ref="<?= url::site("organize/content/__ITEM_ID__/__OFFSET__") ?>">
          <ul id="gMicroThumbGrid">
            <?= $micro_thumb_grid ?>
          </ul>
        </div>
        <div id="gOrganizeEditDrawer" class="yui-u">
          <div id="gOrganizeEditDrawerPanel" class="yui-gf">
          </div>
          <div id="gOrganizeEditDrawerHandle">
            <div id="gOrganizeEditHandleButtonsRight">
              <a id="gMicroThumbDone" href="#" ref="done"
                 class="gButtonLink ui-corner-all ui-state-default"><?= t("Close") ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $("#gOrganize").ready($.organize.init);
</script>
