<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="graphicsmagick" class="g-block<?= $is_active ? " g-selected" : "" ?><?= $tk->installed ? "  g-installed-toolkit" : " g-unavailable" ?>">
  <img class="logo" width="107" height="76" src="<?= url::file("modules/gallery/images/graphicsmagick.png"); ?>" alt="<? t("Visit the GraphicsMagick project site") ?>" />
  <h3> <?= t("GraphicsMagick") ?> </h3>
  <p>
    <?= t("GraphicsMagick is a standalone graphics program available on most Linux systems.  Please refer to the <a href=\"%url\">GraphicsMagick website</a> for more information.",
        array("url" => "http://www.graphicsmagick.org")) ?>
  </p>
  <? if ($tk->installed): ?>
  <div class="g-module-status g-info">
    <?= t("GraphicsMagick version %version is available in %dir", array("version" => $tk->version, "dir" => $tk->dir)) ?>
  </div>
  <p>
    <a class="g-button ui-state-default ui-corner-all"><?= t("Activate Graphics Magic") ?></a>
  </p>
  <? else: ?>
  <div class="g-module-status g-warning">
    <?= $tk->error ?>
  </div>
  <? endif ?>
</div>
