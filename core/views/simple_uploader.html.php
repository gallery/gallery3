<?php defined("SYSPATH") or die("No direct script access.") ?>
<!-- hack to get this string into the dialog's titlebar -->
<form id="gAddPhotos" action="<?= url::site("simple_uploader/finish") ?>">
  <fieldset>
    <legend> <?= t("Add photos to %album_title", array("album_title" => $item->title)) ?> </legend>
  </fieldset>

  <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
          id="SimpleUploader"
          width="470px"
          height="400px"
          codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
    <param name="movie" value="<?= url::file("core/SimpleUploader.swf") ?>" />
    <param name="flashVars" value="<?= $flash_vars ?>" />
    <param name="quality" value="high" />
    <param name="bgcolor" value="#ffffff" />
    <param name="allowScriptAccess" value="sameDomain" />
    <embed src="<?= url::file("core/SimpleUploader.swf") ?>"
           quality="high"
           bgcolor="#ffffff"
           flashVars="<?= $flash_vars ?>"
           width="470" height="400" name="<?= url::file("core/SimpleUploader.swf") ?>"
           align="middle"
           play="true"
           loop="false"
           quality="high"
           allowScriptAccess="sameDomain"
           type="application/x-shockwave-flash"
           pluginspage="http://www.adobe.com/go/getflashplayer">
    </embed>
  </object>

  <input type="submit" value="<?= t("Finish") ?>">
</form>
