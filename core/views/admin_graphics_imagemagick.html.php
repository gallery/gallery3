<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="imagemagick" class="gBlock<?= $is_active ? " gSelected" : "" ?><?= $tk->imagemagick ? "" : " gUnavailable" ?>">
  <h3> <?= t("ImageMagick") ?> </h3>
  <img class="logo" width="114" height="118" src="<?= url::file("core/images/imagemagick.jpg"); ?>" alt="<? t("Visit the ImageMagick project site") ?>" />
  <p>
    <?= t("ImageMagick is a standalone graphics program available on most Linux systems.  Please refer to the <a href=\"%url\">ImageMagick website</a> for more information.",
        array("url" => "http://www.imagemagick.org")) ?>
  </p>
  <? if ($tk->imagemagick): ?>
  <p class="gSuccess">
    <?= t("ImageMagick is available in %path", array("path" => $tk->imagemagick)) ?>
  </p>
  <? else: ?>
  <p class="gInfo">
    <?= t("ImageMagick is not available on your system.") ?>
  </p>
  <? endif ?>
</div>
