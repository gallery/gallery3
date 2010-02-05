<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var move_url = "<?= url::site("organize/move_to/__ALBUM_ID__?csrf=$csrf") ?>";
  var rearrange_url = "<?= url::site("organize/rearrange/__TARGET_ID__/__BEFORE__?csrf=$csrf") ?>";
  var sort_order_url = "<?= url::site("organize/sort_order/__ALBUM_ID__/__COL__/__DIR__?csrf=$csrf") ?>";
  var tree_url = "<?= url::site("organize/tree/__ALBUM_ID__") ?>";
</script>
<div id="g-organize" class="g-dialog-panel">
  <h1 style="display:none"><?= t("Organize %name", array("name" => html::purify($album->title))) ?></h1>
  <div id="g-organize-content-pane">
    <div id="g-organize-tree-container" class="g-left ui-helper-clearfix">
      <h3><?= t("Albums") ?></h3>
      <ul id="g-organize-album-tree">
        <?= $album_tree ?>
      </ul>
    </div>
    <div id="g-organize-detail" class="g-left ui-helper-clearfix">
      <ul id="g-action-status" class="g-message-block">
        <li class="g-info"><?= t("Drag and drop photos to re-order or move between albums") ?></li>
      </ul>
      <div id="g-organize-microthumb-grid" class="ui-widget"
           ref="<?= url::site("organize/album/__ITEM_ID__/__OFFSET__") ?>">
          <?= $micro_thumb_grid ?>
      </div>
      <div id="g-organize-controls" class="ui-widget-header">
        <a id="g-organize-close" href="#" ref="done"
           class="g-button g-right ui-corner-all ui-state-default"><?= t("Close") ?></a>
        <form>
          <ul>
            <li id="g-organize-sort-order-text" class="g-left"><?= t("Sort order") ?></li>
            <li class="g-left">
          <?= form::dropdown(array("id" => "g-organize-sort-column"), album::get_sort_order_options(), $album->sort_column) ?></li><li class="g-left">
          <?= form::dropdown(array("id" => "g-organize-sort-order"), array("ASC" => "Ascending", "DESC" => "Descending"), $album->sort_order) ?></li></ul>
        </form>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $("#g-organize").ready($.organize.init);
</script>
