<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript" src="<?php echo  url::file("lib/swfobject.js") ?>"></script>
<script type="text/javascript" src="<?php echo  url::file("lib/uploadify/jquery.uploadify.min.js") ?>"></script>
<script type="text/javascript">
  <?php $flash_minimum_version = "9.0.24" ?>
  var success_count = 0;
  var error_count = 0;
  var updating = 0;
  $("#g-add-photos-canvas").ready(function () {
    var update_status = function() {
      if (updating) {
        // poor man's mutex
        setTimeout(function() { update_status(); }, 500);
      }
      updating = 1;
      $.get("<?php echo  url::site("uploader/status/_S/_E") ?>"
            .replace("_S", success_count).replace("_E", error_count),
          function(data) {
            $("#g-add-photos-status-message").html(data);
            updating = 0;
          });
    };

    if (swfobject.hasFlashPlayerVersion("<?php echo  $flash_minimum_version ?>")) {
      $("#g-uploadify").uploadify({
        width: 298,
        height: 32,
        uploader: "<?php echo  url::file("lib/uploadify/uploadify.swf.php") ?>",
        script: "<?php echo  url::site("uploader/add_photo/{$album->id}") ?>",
        scriptData: <?php echo  json_encode($script_data) ?>,
        fileExt: "<?php echo  implode(";", $extensions) ?>",
        fileDesc: <?php echo  t("Photos and movies")->for_js() ?>,
        cancelImg: "<?php echo  url::file("lib/uploadify/cancel.png") ?>",
        simUploadLimit: <?php echo  $simultaneous_upload_limit ?>,
        sizeLimit: <?php echo  $size_limit_bytes ?>,
        wmode: "transparent",
        hideButton: true, /* should be true */
        auto: true,
        multi: true,
        onAllComplete: function(filesUploaded, errors, allbytesLoaded, speed) {
          $("#g-upload-cancel-all")
            .addClass("ui-state-disabled")
            .attr("disabled", "disabled");
          $("#g-upload-done")
            .removeClass("ui-state-disabled")
            .attr("disabled", null);
          return true;
        },
        onClearQueue: function(event) {
          $("#g-upload-cancel-all")
            .addClass("ui-state-disabled")
            .attr("disabled", "disabled");
          $("#g-upload-done")
            .removeClass("ui-state-disabled")
            .attr("disabled", null);
          return true;
        },
        onComplete: function(event, queueID, fileObj, response, data) {
          var re = /^error: (.*)$/i;
          var msg = re.exec(response);
          $("#g-add-photos-status ul").append(
            "<li id=\"q" + queueID + "\" class=\"g-success\"><span></span> - " +
            <?php echo  t("Completed")->for_js() ?> + "</li>");
          $("#g-add-photos-status li#q" + queueID + " span").text(fileObj.name);
          setTimeout(function() { $("#q" + queueID).slideUp("slow").remove() }, 5000);
          success_count++;
          update_status();
          return true;
        },
        onError: function(event, queueID, fileObj, errorObj) {
          if (errorObj.type == "HTTP") {
            if (errorObj.info == "500") {
              error_msg = <?php echo  t("Unable to process this photo")->for_js() ?>;
            } else if (errorObj.info == "404") {
              error_msg = <?php echo  t("The upload script was not found")->for_js() ?>;
            } else if (errorObj.info == "400") {
              error_msg = <?php echo  t("This photo is too large (max is %size bytes)",
                                array("size" => $size_limit))->for_js() ?>;
            } else {
              msg += (<?php echo  t("Server error: __INFO__ (__TYPE__)")->for_js() ?>
                .replace("__INFO__", errorObj.info)
                .replace("__TYPE__", errorObj.type));
            }
          } else if (errorObj.type == "File Size") {
            error_msg = <?php echo  t("This photo is too large (max is %size bytes)",
                              array("size" => $size_limit))->for_js() ?>;
          } else {
            error_msg = <?php echo  t("Server error: __INFO__ (__TYPE__)")->for_js() ?>
                        .replace("__INFO__", errorObj.info)
                        .replace("__TYPE__", errorObj.type);
          }
          msg = " - <a target=\"_blank\" href=\"http://codex.galleryproject.org/Gallery3:Troubleshooting:Uploading\">" +
            error_msg + "</a>";

          $("#g-add-photos-status ul").append(
            "<li id=\"q" + queueID + "\" class=\"g-error\"><span></span>" + msg + "</li>");
          $("#g-add-photos-status li#q" + queueID + " span").text(fileObj.name);
          $("#g-uploadify").uploadifyCancel(queueID);
          error_count++;
          update_status();
        },
        onSelect: function(event) {
          if ($("#g-upload-cancel-all").hasClass("ui-state-disabled")) {
            $("#g-upload-cancel-all")
              .removeClass("ui-state-disabled")
              .attr("disabled", null);
            $("#g-upload-done")
              .addClass("ui-state-disabled")
              .attr("disabled", "disabled");
          }
          return true;
        }
      });
    } else {
      $(".requires-flash").hide();
      $(".no-flash").show();
    }
  });
</script>

<div class="requires-flash">
  <?php if ($suhosin_session_encrypt || (identity::active_user()->admin && !$movies_allowed)): ?>
  <div class="g-message-block">
    <?php if ($suhosin_session_encrypt): ?>
    <p class="g-error">
      <?php echo  t("Error: your server is configured to use the <a href=\"%encrypt_url\"><code>suhosin.session.encrypt</code></a> setting from <a href=\"%suhosin_url\">Suhosin</a>.  You must disable this setting to upload photos.",
          array("encrypt_url" => "http://www.hardened-php.net/suhosin/configuration.html#suhosin.session.encrypt",
      "suhosin_url" => "http://www.hardened-php.net/suhosin/")) ?>
    </p>
    <?php endif ?>

    <?php if (identity::active_user()->admin && !$movies_allowed): ?>
    <p class="g-warning">
      <?php echo  t("Movie uploading is disabled on your system. <a href=\"%help_url\">Help!</a>", array("help_url" => url::site("admin/movies"))) ?>
    </p>
    <?php endif ?>
  </div>
  <?php endif ?>

  <div>
    <ul class="g-breadcrumbs">
      <?php foreach ($album->parents() as $i => $parent): ?>
      <li<?php if ($i == 0) print " class=\"g-first\"" ?>> <?php echo  html::clean($parent->title) ?> </li>
      <?php endforeach ?>
      <li class="g-active"> <?php echo  html::purify($album->title) ?> </li>
    </ul>
  </div>

  <div id="g-add-photos-canvas">
    <button id="g-add-photos-button" class="g-button ui-state-default ui-corner-all" href="#"><?php echo  t("Select photos (%size max per file)...", array("size" => $size_limit)) ?></button>
    <span id="g-uploadify"></span>
  </div>
  <div id="g-add-photos-status">
    <ul id="g-action-status" class="g-message-block">
    </ul>
  </div>
</div>

<div class="no-flash" style="display: none">
  <p>
    <?php echo  t("Your browser must have Adobe Flash Player version %flash_minimum_version or greater installed to use this feature.", array("flash_minimum_version" => $flash_minimum_version)) ?>
  </p>
  <a href="http://www.adobe.com/go/getflashplayer">
    <img src="<?php echo  request::protocol() ?>://www.adobe.com/images/shared/download_buttons/get_flash_player.gif"
         alt=<?php echo  t("Get Adobe Flash Player")->for_js() ?> />
  </a>
</div>
