<?php defined("SYSPATH") or die("No direct script access.") ?>
<link rel="stylesheet" type="text/css" href="<?php echo url::file("modules/organize/css/organize_dialog.css") ?>" />
<script type="text/javascript">
  var ORGANIZE_TITLE =
    <?php echo t("Organize :: %album_title", array("album_title" => "__TITLE__"))->for_js() ?>;
  var done_organizing = function(album_id) {
    $("#g-dialog").dialog("close");
    window.location = '<?php echo url::site("items/__ID__") ?>'.replace("__ID__", album_id);
  }

  var set_title = function(title) {
    $("#g-dialog").dialog("option", "title", ORGANIZE_TITLE.replace("__TITLE__", title));
  }
  set_title("<?php echo html::clean($album->title) ?>");

  var done_loading = function() {
    $("#g-organize-app-loading").hide();
  }
</script>
<div id="g-organize-app-loading">&nbsp;</div>
<iframe id="g-organize-frame" src="<?php echo url::site("organize/frame/{$album->id}") ?>">
</iframe>

