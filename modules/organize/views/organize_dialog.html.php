<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript" src="<?= url::file("lib/swfobject.js") ?>"></script>
<style type="text/css" media="screen">
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
    if ($(this).data("reload.location")) {
      window.location = $(this).data("reload.location");
    } else {
      window.location.reload();
    }
  });

  function closeOrganizeDialog() {
    $("#g-dialog").dialog("close");
  }

  function setLocation(url) {
    $("#g-dialog").data("reload.location", url);
  }

  function setTitle(title) {
    $("#ui-dialog-title-g-dialog").text(<?= t("Organize :: ")->for_js() ?> + title);
  }

  function getOrganizeStyles() {
    return {
      color: colorToHex($("#g-organize").css("color")),
      backgroundColor: colorToHex($("#g-organize").css("backgroundColor")),
      borderColor: colorToHex($("#g-organize").css("borderLeftColor")),
      rollOverColor: colorToHex($("#g-organize-hover").css("backgroundColor")),
      selectionColor: colorToHex($("#g-organize-active").css("backgroundColor"))
    };
  }

  function colorToHex(color) {
    // Surprising no one, the color extracted from the css is in a different format
    // in IE than it is when extracted from FF or Chrome.  FF and Chrome return
    // the of "rgb(nn,nn,nn)". Where as IE returns it as #hhhhhh.

    if (color.indexOf("#") === 0) {
      return '0x' + color.substring(1);
    } else {
      var digits = /(.*?)rgb\((\d+), (\d+), (\d+)\)/.exec(color);

      var red = parseInt(digits[2]);
      var green = parseInt(digits[3]);
      var blue = parseInt(digits[4]);

      var rgb = blue | (green << 8) | (red << 16);
      return digits[1] + '0x' + rgb.toString(16);
    }
  }

  function getTextStrings() {
    return {
      statusText: <?= t("Drag and drop photos to re-order or move between album")->for_js() ?>,
      remoteError:
        <?= t("Remote server error, please contact your gallery administrator")->for_js() ?>,
      addAlbumError: <?= t("The above highlighted fields are invalid")->for_js() ?>,
      errorOccurred: <?= t("Remote error ocurred")->for_js() ?>,
      addAlbum: <?= t("Add album")->for_js() ?>,
      addImages: <?= t("Add photo")->for_js() ?>,
      deleteSelected: <?= t("Delete")->for_js() ?>,
      uploadedText: <?= t("Uploaded {0}")->for_js() ?>,
      removeFileText: <?= t("Remove")->for_js() ?>,
      progressLabel:  <?= t("Completed image %1 of %2")->for_js() ?>,
      uploadLabel:  <?= t("Loaded %1 of %2 bytes")->for_js() ?>,
      moveTitle:  <?= t("Move images")->for_js() ?>,
      deleteTitle:  <?= t("Delete image")->for_js() ?>,
      uploadTitle:  <?= t("Upload image")->for_js() ?>,
      cancel: <?= t("Cancel")->for_js() ?>,
      close: <?= t("Close")->for_js() ?>
    };
  }

  function getGalleryParameters() {
    return {
      domain: "<?= $domain ?>",
      accessKey: "<?= $access_key ?>",
      protocol: "<?= request::protocol() ?>",
      fileFilter: "<?= $file_filter ?>",
      sortOrder: "<?= $sort_order ?>",
      sortFields: "<?= $sort_fields ?>",
      albumId: "<?= $album->id ?>",
      selectedId: "<?= $selected_id ?>",
      restUri: "<?= $rest_uri ?>",
      controllerUri: "<?= $controller_uri ?>"
    };
  };

  // For version detection, set to minimum required Flash Player version, or 0 (or 0.0.0),
  // for no version detection.
  var swfVersionStr = "<?= $flash_minimum_version = "10.0.0" ?>";

  // To use express install, set to playerProductInstall.swf, otherwise the empty string.
  var xiSwfUrlStr = "";
  var flashvars = {};

  var size = $.gallery_get_viewport_size();

  var params = {};
  params.quality = "high";
  params.bgcolor = "#ffffff";
  params.allowNetworking = "all";
  params.allowscriptaccess = "sameDomain";
  params.allowfullscreen = "true";
  var attributes = {};
  attributes.id = "Gallery3WebClient";
  attributes.name = "Gallery3WebClient";
  attributes.align = "middle";
  swfobject.embedSWF("<?= $swf_uri ?>",
                     "flashContent", size.width() - 100,  size.height() - 135,
                     swfVersionStr, xiSwfUrlStr, flashvars, params, attributes);
</script>
<div id="g-organize" class="g-dialog-panel">
    <!-- The following spans are placeholders so we can load the hover and active styles for the flex component -->
    <span id="g-organize-hover" /><span id="g-organize-active" />
  <h1 style="display:none"><?= t("Organize :: %name", array("name" => html::purify($album->title))) ?></h1>
  <div id="flashContent">
    <p>
      <?= t("Your browser must have Adobe Flash Player version %flash_minimum_version or greater installed to use this feature.", array("flash_minimum_version" => $flash_minimum_version)) ?>
    </p>
    <a href="http://www.adobe.com/go/getflashplayer">
      <img src="<?= request::protocol() ?>://www.adobe.com/images/shared/download_buttons/get_flash_player.gif"
           alt=<?= t("Get Adobe Flash Player")->for_js() ?> />
    </a>
  </div>
</div>
