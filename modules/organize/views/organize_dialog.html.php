<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript" src="<?= url::file("lib/swfobject.js") ?>"></script>
<style type="text/css" media="screen">
  #flashContent {
    display:none;
  }

  .g-organize {
    padding: 0;
    margins: 0;
  }

  object {
    display: block;
    outline: none;
  }

  #g-dialog {
    padding: 0;
  }
</style>

<script type="text/javascript">
      $("#g-dialog").bind("dialogclose", function(event, ui) {
        // @todo do a call to organize/closing to end the batch
        window.location.reload();
      });

  function closeOrganizeDialog() {
    console.log("closeOrganizeDialog");
    $("#g-dialog").dialog("close");
  }

  function getOrganizeStyles() {
    var styles = {
      "color": colorToHex($("#g-organize").css("color")),
      "backgroundColor": colorToHex($("#g-organize").css("backgroundColor")),
      "borderColor": colorToHex($("#g-organize").css("borderLeftColor")),
      "rollOverColor": colorToHex($("#g-organize-hover").css("backgroundColor")),
      "selectionColor": colorToHex($("#g-organize-active").css("backgroundColor"))
    };
    return styles;
  }

  function colorToHex(color) {
    var digits = /(.*?)rgb\((\d+), (\d+), (\d+)\)/.exec(color);

    var red = parseInt(digits[2]);
    var green = parseInt(digits[3]);
    var blue = parseInt(digits[4]);

    var rgb = blue | (green << 8) | (red << 16);
    return digits[1] + '0x' + rgb.toString(16);
  }

  function getTextStrings() {
    var strings = {
      "statusText": <?= t("Drag and drop photos to re-order or move between album")->for_js() ?>,
      "remoteError":
        <?= t("Remote server error, please contact your gallery administrator")->for_js() ?>,
      "addAlbumError": <?= t("The above highlighted fields are invalid")->for_js() ?>,
      "errorOccurred": <?= t("Remote error ocurred")->for_js() ?>,
      "addAlbum": <?= t("Add album")->for_js() ?>,
      "addImages": <?= t("Add photo")->for_js() ?>,
      "deleteSelected": <?= t("Delete")->for_js() ?>,
      "uploadedText": <?= t("Uploaded {0}")->for_js() ?>,
      "removeFileText": <?= t("Remove")->for_js() ?>,
      "bytes": <?= t("{0} bytes")->for_js() ?>,
      "kilobytes": <?= t("{0} KB")->for_js() ?>,
      "megabytes": <?= t("{0} MB")->for_js() ?>,
      "gigabytes": <?= t("{0} GB")->for_js() ?>,
      "progressLabel":  <?= t("Completed image %1 of %2")->for_js() ?>,
      "uploadLabel":  <?= t("Loaded %1 of %2 bytes")->for_js() ?>,
      "moveTitle":  <?= t("Move images")->for_js() ?>,
      "deleteTitle":  <?= t("Delete image")->for_js() ?>,
      "uploadTitle":  <?= t("Upload image")->for_js() ?>,
      "cancel": <?= t("Cancel")->for_js() ?>,
      "close": <?= t("Close")->for_js() ?>
    };
    return strings;
  }

  /*
    For version detection, set to min. required Flash Player version, or 0 (or 0.0.0),
    for no version detection.
  */
  var swfVersionStr = "0.0.0";
  /* To use express install, set to playerProductInstall.swf, otherwise the empty string.*/
  var xiSwfUrlStr = "";
  var flashvars = {
    fileFilter: '<?= $file_filter ?>',
    domains: '["<?= $domain ?>"]',
    sortOrder: '<?= $sort_order ?>',
    sortFields: '<?= $sort_fields ?>',
    baseUrl: '<?= $base_url ?>',
    apiKey: '<?= $api_key ?>',
    albumId: "<?= $album->id ?>",
    controller: '<?= url::abs_site("organize") ?>/'
  };

  var size = $.gallery_get_viewport_size();

  var params = {};
  params.quality = "high";
  params.bgcolor = "#ffffff";
  params.allowNetworking = "all";
  params.allowscriptaccess = "sameDomain";
  params.allowfullscreen = "true";
  var attributes = {};
  attributes.id = "g-organize-object";
  attributes.name = "organize";
  attributes.align = "middle";
  swfobject.embedSWF("<?= url::file("modules/organize/lib/Gallery3WebClient.swf") ?>",
                     "flashContent", size.width() - 100,  size.height() - 135,
                     swfVersionStr, xiSwfUrlStr, flashvars, params, attributes);
</script>
<div id="g-organize" class="g-dialog-panel">
    <!-- The following spans are placeholders so we can load the hover and active styles for the flex component -->
    <span id="g-organize-hover" /><span id="g-organize-active" />
  <h1 style="display:none"><?= t("Organize %name", array("name" => html::purify($album->title))) ?></h1>
    <div id="flashContent">&nbsp;</div>
</div>
