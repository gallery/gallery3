<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gd" class="g-block<?php echo  $is_active ? " g-selected" : "" ?><?php echo  $tk->installed ? " g-installed-toolkit" : " g-unavailable" ?>">
  <img class="logo" width="170" height="110" src="<?php echo  url::file("modules/gallery/images/gd.png"); ?>" alt="<?php t("Visit the GD lib project site") ?>" />
  <h3> <?php echo  t("GD") ?> </h3>
  <p>
    <?php echo  t("The GD graphics library is an extension to PHP commonly installed most webservers.  Please refer to the <a href=\"%url\">GD website</a> for more information.",
        array("url" => "http://www.boutell.com/gd")) ?>
  </p>
  <?php if ($tk->installed && $tk->rotate): ?>
  <div class="g-module-status g-info">
    <?php echo  t("You have GD version %version.", array("version" => $tk->version)) ?>
  </div>
  <p>
    <a class="g-button ui-state-default ui-corner-all"><?php echo  t("Activate GD") ?></a>
  </p>
  <?php elseif ($tk->installed): ?>
  <?php if ($tk->error): ?>
  <p class="g-module-status g-warning">
    <?php echo  $tk->error ?>
  </p>
  <?php endif ?>
  <p>
    <a class="g-button ui-state-default ui-corner-all"><?php echo  t("Activate GD") ?></a>
  </p>
  <?php else: ?>
  <div class="g-module-status g-info">
    <?php echo  t("You do not have GD installed.") ?>
  </div>
  <?php endif ?>
</div>
