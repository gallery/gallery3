<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript" src="<?= url::file("lib/swfobject.js") ?>"></script>
<script type="text/javascript" src="<?= url::file("lib/uploadify/jquery.uploadify.min.js") ?>"></script>
<script type="text/javascript">
  $("#g-add-photos-canvas").ready(function () {
    $("#g-uploadify").uploadify({
      width: 150,
      height: 33,
      uploader: "<?= url::file("lib/uploadify/uploadify.swf") ?>",
      script: "<?= url::site("uploader/add_photo/{$album->id}") ?>",
      scriptData: <?= json_encode($script_data) ?>,
      fileExt: "*.gif;*.jpg;*.jpeg;*.png;*.flv;*.mp4;*.m4v;*.GIF;*.JPG;*.JPEG;*.PNG;*.FLV;*.MP4;*.M4V",
      fileDesc: <?= t("Photos and movies")->for_js() ?>,
      cancelImg: "<?= url::file("lib/uploadify/cancel.png") ?>",
      simUploadLimit: <?= $simultaneous_upload_limit ?>,
      wmode: "transparent",
      hideButton: true, /* should be true */
      auto: true,
      multi: true,
      onAllComplete: function(filesUploaded, errors, allbytesLoaded, speed) {
        $("#g-upload-cancel-all")
          .addClass("ui-state-disabled")
          .attr("disabled", "disabled");
        return true;
      },
      onClearQueue: function(event) {
        $("#g-upload-cancel-all")
          .addClass("ui-state-disabled")
          .attr("disabled", "disabled");
        return true;
      },
      onComplete: function(event, queueID, fileObj, response, data) {
        var re = /^error: (.*)$/i;
        var msg = re.exec(response);
        if (msg) {
          $("#g-add-photos-status ul").append(
            "<li class=\"g-error\">" + fileObj.name + " - " + msg[1] + "</li>");
        } else {
          $("#g-add-photos-status ul").append(
            "<li class=\"g-success\">" + fileObj.name + " - " + <?= t("Completed")->for_js() ?> + "</li>");
        }
        return true;
      },
      onError: function(event, queueID, fileObj, errorObj) {
        var msg = " - ";
        if (errorObj.type == "HTTP") {
          if (errorObj.info == "500") {
            msg += <?= t("Unable to process this file")->for_js() ?>;
            // Server error - check server logs
          } else if (errorObj.info == "404") {
            msg += <?= t("The upload script was not found.")->for_js() ?>;
            // Server script not found
          } else {
            // Server Error: status: errorObj.info
            msg += (<?= t("Server error: __INFO__")->for_js() ?>.replace("__INFO__", errorObj.info));
          }
        } else if (errorObj.type == "File Size") {
          var sizelimit = $("#g-uploadify").uploadifySettings(sizeLimit);
          msg += fileObj.name+' '+errorObj.type+' Limit: '+Math.round(d.sizeLimit/1024)+'KB';
        } else {
          msg += (<?= t("Server error: __INFO__ (__TYPE__)")->for_js() ?>
            .replace("__INFO__", errorObj.info)
            .replace("__TYPE__", errorObj.type));
        }
        $("#g-add-photos-status ul").append(
          "<li class=\"g-error\">" + fileObj.name + msg + "</li>");
        $("#g-uploadify" + queueID).remove();
      },
      onSelect: function(event) {
        if ($("#g-upload-cancel-all").hasClass("ui-state-disabled")) {
          $("#g-upload-cancel-all")
            .removeClass("ui-state-disabled")
            .attr("disabled", null);
        }
        return true;
      }
    });
  });
</script>

<? if (ini_get("suhosin.session.encrypt")): ?>
<ul id="g-action-status" class="g-message-block">
  <li class="g-error">
    <?= t("Error: your server is configured to use the <a href=\"%encrypt_url\"><code>suhosin.session.encrypt</code></a> setting from <a href=\"%suhosin_url\">Suhosin</a>.  You must disable this setting to upload photos.",
        array("encrypt_url" => "http://www.hardened-php.net/suhosin/configuration.html#suhosin.session.encrypt",
    "suhosin_url" => "http://www.hardened-php.net/suhosin/")) ?>
  </li>
</ul>
<? endif ?>

<div>
  <p>
    <?= t("Photos will be uploaded to album: ") ?>
  </p>
  <ul class="g-breadcrumbs ui-helper-clearfix">
    <? foreach ($album->parents() as $i => $parent): ?>
    <li<? if ($i == 0) print " class=\"g-first\"" ?>> <?= html::clean($parent->title) ?> </li>
    <? endforeach ?>
    <li class="g-active"> <?= html::purify($album->title) ?> </li>
  </ul>
</div>

<div id="g-add-photos-canvas">
  <button id="g-add-photos-button" class="g-button ui-state-default ui-corner-all" href="#"><?= t("Select photos...") ?></button>
  <span id="g-uploadify"></span>
</div>
<div id="g-add-photos-status">
  <ul id="g-action-status" class="g-message-block">
  </ul>
</div>
