<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript" src="<?= url::file("lib/swfupload/swfupload.js") ?>"></script>
<script type="text/javascript" src="<?= url::file("lib/swfupload/swfupload.queue.js") ?>"></script>

<!-- hack to set the title for the dialog -->
<form id="gAddPhotosForm" action="<?= url::site("simple_uploader/finish") ?>">
  <fieldset>
    <legend> <?= t("Add photos to %album_title", array("album_title" => $item->title)) ?> </legend>
  </fieldset>
</form>

<div id="gAddPhotos">
  <? if (ini_get("suhosin.session.encrypt")): ?>
  <div class="gError">
    <?= t("Error: your server is configured to use the <a href=\"%encrypt_url\"><code>suhosin.session.encrypt</code></a> setting from <a href=\"%suhosin_url\">Suhosin</a>.  You must disable this setting to upload photos.",
        array("encrypt_url" => "http://www.hardened-php.net/suhosin/configuration.html#suhosin.session.encrypt",
    "suhosin_url" => "http://www.hardened-php.net/suhosin/")) ?>
  </div>
  <? endif ?>

  <p>
    <?= t("Photos will be uploaded to album: ") ?>
  </p>
  <ul class="gBreadcrumbs">
    <? foreach ($item->parents() as $parent): ?>
    <li> <?= $parent->title ?> </li>
    <? endforeach ?>
    <li class="active"> <?= $item->title ?> </li>
  </ul>

  <p><?= t("Upload Queue") ?></p>
  <div id="gAddPhotosCanvas" style="text-align: center;">
    <div id="gAddPhotosQueue"></div>
    <div id="gEditPhotosQueue"></div>
    <span id="gChooseFilesButtonPlaceholder"></span>
  </div>
  <button id="gUploadCancel" class="ui-state-default ui-corner-all" type="button"
          onclick="swfu.cancelQueue();"
          disabled="disabled">
    <?= t("Cancel all") ?>
  </button>

  <!-- Proxy the done request back to our form, since its been ajaxified -->
  <button class="ui-state-default ui-corner-all" onclick="$('#gAddPhotosForm').submit()">
    <?= t("Done") ?>
  </button>
</div>

<style>
  #SWFUpload_0 {
    margin-top: 100px;
  }
  #gAddPhotos .gBreadcrumbs {
    border: 0;
    margin: 0;
    padding-left:10px;
  }
  #gAddPhotosCanvas {
    border: 1px solid  #CCCCCC;
    margin: .5em 0 .5em 0;
    width: 469px;
  }
  #gAddPhotos button {
    margin-bottom: .5em;
    float: right;
  }
  #gAddPhotos #gUploadCancel {
    float: left;
  }
</style>

<script type="text/javascript">
  var swfu = new SWFUpload({
    flash_url : "<?= url::file("lib/swfupload/swfupload.swf") ?>",
    upload_url: "<?= url::site("simple_uploader/add_photo/$item->id") ?>",
    post_params: {
      "g3sid": "<?= Session::instance()->id() ?>",
      "user_agent": "<?= Input::instance()->server("HTTP_USER_AGENT") ?>",
      "csrf": "<?= $csrf ?>"
    },
    file_size_limit : "100 MB",
    file_types : "*.gif;*.jpg;*.jpeg;*.png;*.flv;*.mp4;*.GIF;*.JPG;*.JPEG;*.PNG;*.FLV;*.MP4",
    file_types_description : "<?= t("Photos and Movies") ?>",
    file_upload_limit : 1000,
    file_queue_limit : 0,
    custom_settings : { },
    debug: false,

    // Button settings
    button_image_url: "<?= url::file("themes/default/images/select-photos-backg.png") ?>",
    button_width: "202",
    button_height: "45",
    button_placeholder_id: "gChooseFilesButtonPlaceholder",
    button_text: '<span class="swfUploadFont">Select photos...</span>',
    button_text_style: ".swfUploadFont { color: #2E6E9E; font-size: 16px; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-weight: bold; }",
    button_text_left_padding: 30,
    button_text_top_padding: 10,

    // The event handler functions are defined in handlers.js
    file_queued_handler : file_queued,
    file_queue_error_handler : file_queue_error,
    file_dialog_complete_handler : file_dialog_complete,
    upload_start_handler : upload_start,
    upload_progress_handler : upload_progress,
    upload_error_handler : upload_error,
    upload_success_handler : upload_success,
    upload_complete_handler : upload_complete,
    queue_complete_handler : queue_complete
  });

  // @todo add support for cancelling individual uploads
  function File_Progress(file) {
    this.box = $("#" + file.id);
    if (!this.box.length) {
      $("#gAddPhotosQueue").append(
        "<div class=\"box\" id=\"" + file.id + "\">" +
        "<div class=\"title\"></div>" +
        "<div class=\"status\"></div>" +
        "<div class=\"progressbar\"></div></div>");
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
    fp.set_status("pending", "<?= t("Pending...") ?>");
    // @todo add cancel button to call this.cancelUpload(file.id)
  }

  function file_queue_error(file, error_code, message) {
    if (error_code === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
      alert("<?= t("You have attempted to queue too many files.") ?>");
      return;
    }

    var fp = new File_Progress(file);
    switch (error_code) {
    case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
      fp.set_status("error", "<?= t("File is too big.") ?>");
      break;
    case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
      fp.set_status("error", "<?= t("Cannot upload empty files.") ?>");
      break;
    case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
      fp.set_status("error", "<?= t("Invalid file type.") ?>");
      break;
    default:
      if (file !== null) {
        fp.set_status("error", "<?= t("Unknown error") ?>");
      }
      break;
    }
  }

  function file_dialog_complete(num_files_selected, num_files_queued) {
    if (num_files_selected > 0) {
      $("#gUploadCancel").enable(true);
    }

    // Auto start the upload
    this.startUpload();
  }

  function upload_start(file) {
    // Do all file validation on the server side.  Update the UI here because in Linux
    // no uploadProgress events are called (limitation in the Linux Flash VM).
    var fp = new File_Progress(file);
    fp.title.html(file.name);
    fp.set_status("uploading", "<?= t("Uploading...") ?>");
    return true;
    // @todo add cancel button to call this.cancelUpload(file.id)
  }

  function upload_progress(file, bytes_loaded, bytes_total) {
    var percent = Math.ceil((bytes_loaded / bytes_total) * 100);
    var fp = new File_Progress(file);
    fp.set_status("uploading", "<?= t("Uploading...") ?>");
    fp.progress_bar.css("visibility", "visible");
    fp.progress_bar.progressbar("value", percent);
  }

  function upload_success(file, serverData) {
    var fp = new File_Progress(file);
    fp.progress_bar.progressbar("value", 100);
    fp.set_status("complete", "<?= t("Complete.") ?>");
  }

  function upload_error(file, error_code, message) {
    var fp = new File_Progress(file);
    switch (error_code) {
    case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
      fp.set_status("error", "<?= t("Upload error: ") ?>" + message);
      break;
    case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
      fp.set_status("error", "<?= t("Upload failed") ?>");
      break;
    case SWFUpload.UPLOAD_ERROR.IO_ERROR:
      fp.set_status("error", "<?= t("Server error") ?>");
      break;
    case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
      fp.set_status("error", "<?= t("Security error") ?>");
      break;
    case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
      fp.set_status("error", "<?= t("Upload limit exceeded") ?>");
      break;
    case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
      fp.set_status("error", "<?= t("Failed validation.  File skipped") ?>");
      break;
    case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
      // If there aren't any files left (they were all cancelled) disable the cancel button
      if (this.getStats().files_queued === 0) {
        $("#gUploadCancel").enable(false);
      }
      fp.set_status("error", "<?= t("Cancelled") ?>");
      break;
    case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
      fp.set_status("error", "<?= t("Stopped") ?>");
      break;
    default:
      fp.set_status("error", "<?= t("Unknown error: ") ?>" + error_code);
      break;
    }
  }

  function upload_complete(file) {
    if (this.getStats().files_queued === 0) {
      $("#gUploadCancel").enable(false);
    }
  }

  // This event comes from the Queue Plugin
  function queue_complete(num_files_uploaded) {
    var status_msg = "<?= t("Uploaded: __COUNT__") ?>";
    $("#gUploadStatus").html(status_msg.replace("__COUNT__", num_files_uploaded));
  }
</script>
