<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="graphicsmagick" class="g-block<?php echo  $is_active ? " g-selected" : "" ?><?php echo  $tk->installed ? "  g-installed-toolkit" : " g-unavailable" ?>">
  <img class="logo" width="107" height="76" src="<?php echo  url::file("modules/gallery/images/graphicsmagick.png"); ?>" alt="<?php t("Visit the GraphicsMagick project site") ?>" />
  <h3> <?php echo  t("GraphicsMagick") ?> </h3>
  <p>
    <?php echo  t("GraphicsMagick is a standalone graphics program available on most Linux systems.  Please refer to the <a href=\"%url\">GraphicsMagick website</a> for more information.",
        array("url" => "http://www.graphicsmagick.org")) ?>
  </p>
  <?php if ($tk->installed): ?>
  <div class="g-module-status g-info">
    <?php echo  t("GraphicsMagick version %version is available in %dir", array("version" => $tk->version, "dir" => $tk->dir)) ?>
  </div>
  <p>
    <a class="g-button ui-state-default ui-corner-all"><?php echo  t("Activate Graphics Magic") ?></a>
  </p>
  <?php else: ?>
  <div class="g-module-status g-warning">
    <?php echo  $tk->error ?>
  </div>
  <?php endif ?>
</div>
