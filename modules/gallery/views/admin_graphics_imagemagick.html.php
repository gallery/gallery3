<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="imagemagick" class="gBlock<?= $is_active ? " gSelected" : "" ?><?= $tk->installed ? "  gInstalledToolkit" : " gUnavailable" ?>">
  <h3> <?= t("ImageMagick") ?> </h3>
  <img class="logo" width="114" height="118" src="<?= url::file("modules/gallery/images/imagemagick.jpg"); ?>" alt="<? t("Visit the ImageMagick project site") ?>" />
  <p>
    <?= t("ImageMagick is a standalone graphics program available on most Linux systems.  Please refer to the <a href=\"%url\">ImageMagick website</a> for more information.",
        array("url" => "http://www.imagemagick.org")) ?>
  </p>
  <? if ($tk->installed): ?>
  <p class="gSuccess">
    <?= t("ImageMagick version %version is available in %dir", array("version" => $tk->version, "dir" => $tk->dir)) ?>
  </p>
  <p>
    <a class="gButtonLink ui-state-default ui-corner-all"><?= t("Activate ImageMagick") ?></a>
  </p>
  <? elseif ($tk->error): ?>
  <p class="gWarning">
    <?= $tk->error ?>
  </p>
  <? endif ?>
</div>
