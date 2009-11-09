<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript" src="<?= url::file("lib/swfobject.js") ?>"></script>
<script type="text/javascript" src="<?= url::file("lib/uploadify/jquery.uploadify.min.js") ?>"></script>
<script type="text/javascript">
  $("#g-add-photos-canvas").ready(function () {
    $("#g-uploadify").uploadify({
      uploader: "<?= url::file("lib/uploadify/uploadify.swf") ?>",
      folder: "<?= url::file("var/uploads") ?>",
      script: "<?= url::site("simple_uploader/add_photo/{$item->id}") ?>",
      scriptData: <?= json_encode(array(
        "g3sid" => Session::instance()->id(),
        "user_agent" => Input::instance()->server("HTTP_USER_AGENT"),
        "tags" => "",
        "csrf" => $csrf)) ?>,
      fileExt: "*.gif;*.jpg;*.jpeg;*.png;*.flv;*.mp4;*.GIF;*.JPG;*.JPEG;*.PNG;*.FLV;*.MP4",
      fileDesc: <?= t("Photos and movies")->for_js() ?>,
      cancelImg: "<?= url::file("lib/uploadify/cancel.png") ?>",
      buttonText: <?= t("Select Files")->for_js() ?>,
      simUploadLimit: 10,
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
      onSelect: function(event) {
        if ($("#g-upload-cancel-all").hasClass("ui-state-disabled")) {
          $("#g-upload-cancel-all")
            .removeClass("ui-state-disabled")
            .attr("disabled", null);
        }
        return true;
      }
    });
  <? if (module::active("tag")): ?>
    $('#g-add-photos-tags').autocomplete(
      '<?= url::site("tags/autocomplete") ?>',
      {max: 30, multiple: true, multipleSeparator: ',', cacheLength: 1}
    );
    $('#g-add-photos-tags').blur(function (event) {
      $("#g-uploadify").uploadifySettings("scriptData", {"tags": $(this).val()});
    });
  <? endif ?>
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

  <? if (module::active("tag")): ?>
  <div style="clear: both;">
  <label for="g-add-photos-tags"><?= t("Add tags to all uploaded files") ?></label>
  <input type="text" id="g-add-photos-tags" name="tags" value="" />
  </div>
  <? endif ?>

    <div id="g-add-photos-canvas" style="text-align: center;">
    <div id="g-uploadify"></div>
  </div>

  <!-- Proxy the done request back to our form, since its been ajaxified -->
  <button id="g-upload-done" class="ui-state-default ui-corner-all" onclick="$('#g-add-photos-form').submit()">
    <?= t("Done") ?>
  </button>
  <button id="g-upload-cancel-all" class="ui-state-default ui-corner-all ui-state-disabled" onclick="$('#g-uploadify').uploadifyClearQueue();return false;" disabled="disabled">
    <?= t("Cancel All") ?>
  </button>

</div>
</form>
