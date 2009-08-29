<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gd" class="gBlock<?= $is_active ? " gSelected" : "" ?><?= $tk->installed ? " gInstalledToolkit" : " gUnavailable" ?>">
  <img class="logo" width="170" height="110" src="<?= url::file("modules/gallery/images/gd.png"); ?>" alt="<? t("Visit the GD lib project site") ?>" />
  <h3> <?= t("GD") ?> </h3>
  <p>
    <?= t("The GD graphics library is an extension to PHP commonly installed most webservers.  Please refer to the <a href=\"%url\">GD website</a> for more information.",
        array("url" => "http://www.boutell.com/gd")) ?>
  </p>
  <? if ($tk->installed && $tk->rotate): ?>
  <div class="gModuleStatus gInfo">
    <?= t("You have GD version %version.", array("version" => $tk->version)) ?>
  </div>
  <p>
    <a class="gButtonLink ui-state-default ui-corner-all"><?= t("Activate GD") ?></a>
  </p>
  <? elseif ($tk->installed): ?>
  <? if ($tk->error): ?>
  <p class="gModuleStatus gWarning">
    <?= $tk->error ?>
  </p>
  <? endif ?>
  <p>
    <a class="gButtonLink ui-state-default ui-corner-all"><?= t("Activate GD") ?></a>
  </p>
  <? else: ?>
  <div class="gModuleStatus gInfo">
    <?= t("You do not have GD installed.") ?>
  </div>
  <? endif ?>
</div>
