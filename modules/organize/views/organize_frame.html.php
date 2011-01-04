<?php defined("SYSPATH") or die("No direct script access.") ?>
<style>
  #g-organize-frame {
    border: 0px;
    width: 100%;
    height: 100%;
  }
</style>
<script type="text/javascript">
  var ORGANIZE_TITLE =
    <?= t("Organize :: %album_title", array("album_title" => "__TITLE__"))->for_js() ?>;
  var done_organizing = function(album_id) {
    $("#g-dialog").dialog("close");
    window.location = '<?= url::site("items/__ID__") ?>'.replace("__ID__", album_id);
  }

  var set_title = function(title) {
    $("#g-dialog").dialog("option", "title", ORGANIZE_TITLE.replace("__TITLE__", title));
  }
  set_title("<?= $album->title ?>");
</script>
<iframe id="g-organize-frame" src="<?= url::site("organize/dialog/{$album->id}") ?>">
</iframe>
