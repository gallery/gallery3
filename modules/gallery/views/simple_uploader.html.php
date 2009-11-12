<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript" src="<?= url::file("lib/swfobject.js") ?>"></script>
<script type="text/javascript" src="<?= url::file("lib/uploadify/jquery.uploadify.min.js") ?>"></script>
<script type="text/javascript">
  $("#g-add-photos-canvas").ready(function () {
    $("#g-uploadify").uploadify({
      uploader: "<?= url::file("lib/uploadify/uploadify.swf") ?>",
      script: "<?= url::site("simple_uploader/add_photo/{$item->id}") ?>",
      scriptData: <?= json_encode(array(
        "g3sid" => Session::instance()->id(),
        "user_agent" => Input::instance()->server("HTTP_USER_AGENT"),
        "csrf" => $csrf)) ?>,
      fileExt: "*.gif;*.jpg;*.jpeg;*.png;*.flv;*.mp4;*.GIF;*.JPG;*.JPEG;*.PNG;*.FLV;*.MP4",
      fileDesc: <?= t("Photos and movies")->for_js() ?>,
      cancelImg: "<?= url::file("lib/uploadify/cancel.png") ?>",
      buttonText: <?= t("Select Photos ...")->for_js() ?>,
      simUploadLimit: 10,
      wmode: "transparent",
      hideButton: true,
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
        // @todo handle a response of "Error: xxxx" as an error
        var re = /^error: (.*)$/i;
        var msg = re.exec(response);
        if (msg) {
          $("#g-add-photos-status ul").append(
            "<li class=\"g-error\">" + fileObj.name + " - " + msg[1] + "</li>");
        } else {
          $("#g-add-photos-status ul").append(
            "<li class=\"g-success\">" + fileObj.name + " - <?= t("Completed") ?></li>");
        }
        return true;
      },

      onError: function(event, queueID, fileObj, errorObj) {
        var msg = " - ";
        if (errorObj.type == "HTTP") {
          if (errorObj.info == "500") {
            msg += "Error occurred processing the file";
            // Server error - check server logs
          } else if (errorObj.info == "404") {
            msg += "The upload script was not found.";
            // Server script not found
          } else {
            // Server Error: status: errorObj.info
            msg += "Error occurred communication with the server, status: " + errorObj.info;
          }
        } else if (errorObj.type == "File Size") {
          var sizelimit = $("#g-uploadify").uploadifySettings(sizeLimit);
          msg += fileObj.name+' '+errorObj.type+' Limit: '+Math.round(d.sizeLimit/1024)+'KB';
        } else {
          msg += "Error occurred communication with the server, error " + errorObj.type + ": " + errorObj.info;
        }
        $("#g-add-photos-status ul").append(
          "<li class=\"g-error\">" + fileObj.name + msg + "</li>");
        $("#g-uploadify" + queueID).remove();
        //return false;
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

    // @todo figure out how to actually get the offset or why it comes back 0 0
    var offset = $("#g-add-photos-button").offset();
    $("#g-uploadifyUploader").css({top: "97px", position: "absolute", left: "198px"});
    $("#g-add-photos-button").height("40px").width("120px");
  });
</script>

<form id="g-add-photos-form" action="<?= url::site("simple_uploader/finish?csrf=$csrf") ?>">
  <fieldset>
    <legend> <?= t("Add photos to %album_title", array("album_title" => html::purify($item->title))) ?> </legend>

  </fieldset>
  <div id="g-add-photos">
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
    <ul class="g-breadcrumbs">
      <? foreach ($item->parents() as $i => $parent): ?>
      <li<? if ($i == 0) print " class=\"g-first\"" ?>> <?= html::clean($parent->title) ?> </li>
      <? endforeach ?>
      <li class="g-active"> <?= html::purify($item->title) ?> </li>
    </ul>
    </div>

    <div id="g-add-photos-canvas" style="text-align: center;">
      <a id="g-add-photos-button" class="ui-corner-all" style="padding-bottom: 1em;" href="#"><?= t("Select Photos...") ?></a>

       <div id="g-uploadify"></div>


    </div>
    <div id="g-add-photos-status" style="text-align: center;">
      <ul>
      </ul>
    </div>

    <!-- Proxy the done request back to our form, since its been ajaxified -->
    <button id="g-upload-done" class="ui-state-default ui-corner-all" onclick="$('#g-add-photos-form').submit();return false;">
      <?= t("Done") ?>
    </button>
    <button id="g-upload-cancel-all" class="ui-state-default ui-corner-all ui-state-disabled" onclick="$('#g-uploadify').uploadifyClearQueue();return false;" disabled="disabled">
      <?= t("Cancel All") ?>
    </button>
  </div>
</form>
