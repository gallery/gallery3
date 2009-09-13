<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var move_url = "<?= url::site("organize/move_to/__ALBUM_ID__?csrf=$csrf") ?>";
  var rearrange_url = "<?= url::site("organize/rearrange/__TARGET_ID__/__BEFORE__?csrf=$csrf") ?>";
  var sort_order_url = "<?= url::site("organize/sort_order/__ALBUM_ID__/__COL__/__DIR__?csrf=$csrf") ?>";
  var tree_url = "<?= url::site("organize/tree/__ALBUM_ID__") ?>";
</script>
<div id="gOrganize" class="gDialogPanel">
  <h1 style="display:none"><?= t("Organize %name", array("name" => html::purify($album->title))) ?></h1>
  <div id="bd">
    <div class="yui-gf">
      <div class="yui-u first">
        <h3><?= t("Albums") ?></h3>
      </div>
      <div id="gMessage" class="yui-u">
        <div class="gInfo"><?= t("Drag and drop photos to re-order or move between albums") ?></div>
      </div>
    </div>
    <div id="gOrganizeContentPane" class="yui-gf">
      <div id="gOrganizeTreeContainer" class="yui-u first">
        <ul id="gOrganizeAlbumTree">
          <?= $album_tree ?>
        </ul>
      </div>
      <div id="gOrganizeDetail" class="yui-u">
        <div id="gOrganizeMicroThumbPanel"
             ref="<?= url::site("organize/album/__ITEM_ID__/__OFFSET__") ?>">
          <ul id="gOrganizeMicroThumbGrid">
            <?= $micro_thumb_grid ?>
          </ul>
        </div>
        <div id="gOrganizeControls">
          <a id="gOrganizeClose" href="#" ref="done"
             class="gButtonLink ui-corner-all ui-state-default"><?= t("Close") ?></a>
          <form>
            <?= t("Sort order") ?>
            <?= form::dropdown(array("id" => "gOrganizeSortColumn"), album::get_sort_order_options(), $album->sort_column) ?>
            <?= form::dropdown(array("id" => "gOrganizeSortOrder"), array("ASC" => "Ascending", "DESC" => "Descending"), $album->sort_order) ?>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $("#gOrganize").ready($.organize.init);
</script>
