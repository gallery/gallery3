<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="graphicsmagick" class="gBlock<?= $is_active ? " gSelected" : "" ?><?= $tk->installed ? "  gInstalledToolkit" : " gUnavailable" ?>">
  <h3> <?= t("GraphicsMagick") ?> </h3>
  <img class="logo" width="107" height="76" src="<?= url::file("modules/gallery/images/graphicsmagick.png"); ?>" alt="<? t("Visit the GraphicsMagick project site") ?>" />
  <p>
    <?= t("GraphicsMagick is a standalone graphics program available on most Linux systems.  Please refer to the <a href=\"%url\">GraphicsMagick website</a> for more information.",
        array("url" => "http://www.graphicsmagick.org")) ?>
  </p>
  <? if ($tk->installed): ?>
  <p class="gSuccess">
    <?= t("GraphicsMagick version %version is available in %dir", array("version" => $tk->version, "dir" => $tk->dir)) ?>
  </p>
  <p>
    <a class="gButtonLink ui-state-default ui-corner-all"><?= t("Activate Graphics Magic") ?></a>
  </p>
  <? else: ?>
  <p class="gWarning">
    <?= $tk->error ?>
  </p>
  <? endif ?>
</div>
