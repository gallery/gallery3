<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript" src="<?= url::file("lib/swfupload/swfupload.js") ?>"></script>
<script type="text/javascript" src="<?= url::file("lib/swfupload/swfupload.queue.js") ?>"></script>
<script type="text/javascript" src="<?= url::file("lib/jquery.scrollTo.js") ?>"></script>

<!-- hack to set the title for the dialog -->
<form id="g-add-photos-form" action="<?= url::site("simple_uploader/finish?csrf=$csrf") ?>">
  <fieldset>
    <legend> <?= t("Add photos to %album_title", array("album_title" => html::purify($item->title))) ?> </legend>
  </fieldset>
</form>

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

  <p>
    <?= t("Photos will be uploaded to album: ") ?>
  </p>
  <ul class="g-breadcrumbs">
    <? $i = 0 ?>
    <? foreach ($item->parents() as $parent): ?>
    <li<? if ($i == 0) print " class=\"g-first\"" ?>> <?= html::clean($parent->title) ?> </li>
    <? $i++ ?>
    <? endforeach ?>
    <li class="g-active"> <?= html::purify($item->title) ?> </li>
  </ul>

  <div id="g-uploadqueue-infobar">
    <?= t("Upload queue") ?>
    <span id="g-uploadstatus"></span>
    <a id="g-cancelupload" title="<?= t("Cancel all the pending uploads")->for_html_attr() ?>" onclick="swfu.cancelQueue();"><?= t("cancel") ?></a>
  </div>
  <div id="g-add-photos-canvas" style="text-align: center;">
    <div id="g-add-photos-queue"></div>
    <div id="g-edit-photos-queue"></div>
  </div>
  <span id="g-choose-files-placeholder"></span>

  <!-- Proxy the done request back to our form, since its been ajaxified -->
  <button class="ui-state-default ui-corner-all" onclick="$('#g-add-photos-form').submit()">
    <?= t("Done") ?>
  </button>
</div>

<script type="text/javascript">
  var swfu = new SWFUpload({
    flash_url: <?= html::js_string(url::file("lib/swfupload/swfupload.swf")) ?>,
    upload_url: <?= html::js_string(url::site("simple_uploader/add_photo/$item->id")) ?>,
    post_params: <?= json_encode(array(
      "g3sid" => Session::instance()->id(),
      "user_agent" => Input::instance()->server("HTTP_USER_AGENT"),
      "csrf" => $csrf)) ?>,
    file_size_limit: <?= html::js_string(ini_get("upload_max_filesize") ? num::convert_to_bytes(ini_get("upload_max_filesize"))."B" : "100MB") ?>,
    file_types: "*.gif;*.jpg;*.jpeg;*.png;*.flv;*.mp4;*.GIF;*.JPG;*.JPEG;*.PNG;*.FLV;*.MP4",
    file_types_description: <?= t("Photos and movies")->for_js() ?>,
    file_upload_limit: 1000,
    file_queue_limit: 0,
    custom_settings: { },
    debug: false,

    // Button settings
    button_image_url: <?= html::js_string(url::file(gallery::find_file("images", "select-photos-backg.png"))) ?>,
    button_width: "202",
    button_height: "45",
    button_placeholder_id: "g-choose-files-placeholder",
    button_text: <?= json_encode('<span class="swfUploadFont">' . t("Select photos...") . '</span>') ?>,
    button_text_style: ".swfUploadFont { color: #2E6E9E; font-size: 16px; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-weight: bold; }",
    button_text_left_padding: 30,
    button_text_right_padding: 30,
    button_text_top_padding: 10,

    // The event handler functions are defined in handlers.js
    file_queued_handler: file_queued,
    file_queue_error_handler: file_queue_error,
    file_dialog_complete_handler: file_dialog_complete,
    upload_start_handler: upload_start,
    upload_progress_handler: upload_progress,
    upload_error_handler: upload_error,
    upload_success_handler: upload_success,
    upload_complete_handler: upload_complete,
    queue_complete_handler: queue_complete
  });

  // @todo add support for cancelling individual uploads
  function File_Progress(file) {
    this.box = $("#" + file.id);
    if (!this.box.length) {
      $("#g-add-photos-queue").append(
        "<div class=\"box\" id=\"" + file.id + "\">" +
        "<div class=\"title\"></div>" +
        "<div class=\"status\"></div>" +
        "<div class=\"progressbar\"></div>" +
        "</div>");
      this.box = $("#" + file.id);
    }
    this.title = this.box.find(".title");
    this.status = this.box.find(".status");
    this.progress_bar = this.box.find(".progressbar");
    this.progress_bar.progressbar();
    this.progress_bar.css("visibility", "hidden");
  }

  File_Progress.prototype.set_status = function(status_class, msg) {
    this.box.removeClass("pending error uploading complete").addClass(status_class);
    this.status.html(msg);
  }

  function file_queued(file) {
    var fp = new File_Progress(file);
    fp.title.html(file.name);
    fp.set_status("pending", <?= t("Pending...")->for_js() ?>);
    // @todo add cancel button to call this.cancelUpload(file.id)
  }

  function file_queue_error(file, error_code, message) {
    if (error_code === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
      alert(<?= t("You have attempted to queue too many files.")->for_js() ?>);
      return;
    }

    var fp = new File_Progress(file);
    switch (error_code) {
    case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
      fp.title.html(file.name);
      fp.set_status("error", <?= t("<strong>File is too big.</strong> A likely error source is a too low value for <em>upload_max_filesize</em> (%upload_max_filesize) in your <em>php.ini</em>.", array("upload_max_filesize" => ini_get("upload_max_filesize")))->for_js() ?>);
      break;
    case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
      fp.title.html(file.name);
      fp.set_status("error", <?= t("Cannot upload empty files.")->for_js() ?>);
      break;
    case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
      fp.title.html(file.name);
      fp.set_status("error", <?= t("Invalid file type.")->for_js() ?>);
      break;
    default:
      if (file !== null) {
        fp.title.html(file.name);
        fp.set_status("error", <?= t("Unknown error")->for_js() ?>);
      }
      break;
    }
  }

  function file_dialog_complete(num_files_selected, num_files_queued) {
    if (num_files_selected > 0) {
      $("#g-cancelupload").show();
      $("#g-uploadstatus").text(get_completed_status_msg(this.getStats()));
    }

    // Auto start the upload
    this.startUpload();
  }

  function upload_start(file) {
    // Do all file validation on the server side.  Update the UI here because in Linux
    // no uploadProgress events are called (limitation in the Linux Flash VM).
    var fp = new File_Progress(file);
    fp.title.html(file.name);
    fp.set_status("uploading", <?= t("Uploading...")->for_js() ?>);
    $("#g-add-photos-canvas").scrollTo(fp.box, 1000);

    // move file select button
    $("#SWFUpload_0").css({'left': '0', 'top': '0'});
    swfu.setButtonText(<?= json_encode('<span class="swfUploadFont">' . t("Select more photos...") . '</span>') ?>);

    return true;
    // @todo add cancel button to call this.cancelUpload(file.id)
  }

  function upload_progress(file, bytes_loaded, bytes_total) {
    var percent = Math.ceil((bytes_loaded / bytes_total) * 100);
    var fp = new File_Progress(file);
    fp.set_status("uploading", <?= t("Uploading...")->for_js() ?>);
    fp.progress_bar.css("visibility", "visible");
    fp.progress_bar.progressbar("value", percent);
  }

  function upload_success(file, serverData) {
    var fp = new File_Progress(file);
    fp.progress_bar.progressbar("value", 100);
    fp.set_status("complete", <?= t("Complete.")->for_js() ?>);
  }

  function upload_error(file, error_code, message) {
    var fp = new File_Progress(file);
    switch (error_code) {
    case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
      fp.set_status("error", <?= t("Upload error: bad image file")->for_js() ?>);
      break;
    case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
      fp.set_status("error", <?= t("Upload failed")->for_js() ?>);
      break;
    case SWFUpload.UPLOAD_ERROR.IO_ERROR:
      fp.set_status("error", <?= t("Server error")->for_js() ?>);
      break;
    case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
      fp.set_status("error", <?= t("Security error")->for_js() ?>);
      break;
    case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
      fp.set_status("error", <?= t("Upload limit exceeded")->for_js() ?>);
      break;
    case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
      fp.set_status("error", <?= t("Failed validation.  File skipped")->for_js() ?>);
      break;
    case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
      // If there aren't any files left (they were all cancelled) disable the cancel button
      if (this.getStats().files_queued === 0) {
        $("#g-cancelupload").hide();
      }
      fp.set_status("error", <?= t("Cancelled")->for_js() ?>);
      break;
    case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
      fp.set_status("error", <?= t("Stopped")->for_js() ?>);
      break;
    default:
      fp.set_status("error", <?= t("Unknown error: ")->for_js() ?> + error_code);
      break;
    }
  }

  function upload_complete(file) {
    var stats = this.getStats();
    $("#g-uploadstatus").text(get_completed_status_msg(stats));
    if (stats.files_queued === 0) {
      $("#g-cancelupload").hide();
    }
  }

  function get_completed_status_msg(stats) {
    var msg = <?= t("(completed %completed of %total)", array("completed" => "__COMPLETED__", "total" => "__TOTAL__"))->for_js() ?>;
    msg = msg.replace("__COMPLETED__", stats.successful_uploads);
    msg = msg.replace("__TOTAL__", stats.files_queued + stats.successful_uploads +
      stats.upload_errors + stats.upload_cancelled + stats.queue_errors);
    return msg;
  }

  // This event comes from the Queue Plugin
  function queue_complete(num_files_uploaded) {
    var status_msg = <?= t("Uploaded: __COUNT__")->for_js() ?>;
    $("#g-upload-status").html(status_msg.replace("__COUNT__", num_files_uploaded));
  }
</script>
