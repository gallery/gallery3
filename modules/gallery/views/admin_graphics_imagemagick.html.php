<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="imagemagick" class="g-block<?php echo $is_active ? " g-selected" : "" ?><?php echo $tk->installed ? "  g-installed-toolkit" : " g-unavailable" ?>">
  <img class="logo" width="114" height="118" src="<?php echo url::file("modules/gallery/images/imagemagick.jpg"); ?>" alt="<?php t("Visit the ImageMagick project site") ?>" />
  <h3> <?php echo t("ImageMagick") ?> </h3>
  <p>
    <?php echo t("ImageMagick is a standalone graphics program available on most Linux systems.  Please refer to the <a href=\"%url\">ImageMagick website</a> for more information.",
        array("url" => "http://www.imagemagick.org")) ?>
  </p>
  <?php if ($tk->installed): ?>
  <div class="g-module-status g-info">
    <?php echo t("ImageMagick version %version is available in %dir", array("version" => $tk->version, "dir" => $tk->dir)) ?>
  </div>
  <p>
    <a class="g-button ui-state-default ui-corner-all"><?php echo t("Activate ImageMagick") ?></a>
  </p>
  <?php elseif ($tk->error): ?>
  <div class="g-module-status g-warning">
    <?php echo $tk->error ?>
  </div>
  <?php endif ?>
</div>
