<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gd" class="gBlock  <?= $tk->gd["GD Version"] ? "" : "unavailable" ?>">
  <img class="logo" width="170" height="110" src="<?= url::file("core/images/gd.png"); ?>" alt="<? t("Visit the GD lib project site") ?>" />
  <h3> <?= t("GD") ?> </h3>
  <p>
    <?= t("The GD graphics library is an extension to PHP commonly installed most webservers.  Please refer to the <a href=\"%url\">GD website</a> for more information.",
        array("url" => "http://www.boutell.com/gd")) ?>
  </p>
  <? if ($tk->gd["GD Version"] && function_exists('imagerotate')): ?>
  <p class="gSuccess">
    <?= t("You have GD version %version.", array("version" => $tk->gd["GD Version"])) ?>
  </p>
  <? elseif ($tk->gd["GD Version"]): ?>
  <p class="gWarning">
    <?= t("You have GD version %version, but it lacks image rotation.",
        array("version" => $tk->gd["GD Version"])) ?>
  </p>
  <? else: ?>
  <p class="gInfo">
    <?= t("You do not have GD installed.") ?>
  </p>
  <? endif ?>
</div>
