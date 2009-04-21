<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gAdminG2Config">
  <h1> <?= t("Gallery 2 Import") ?> </h1>
  <p>
    <?= t("Import your Gallery 2 users, photos, movies, comments and tags into your new Gallery 3 installation.") ?>
  </p>

  <?= $form ?>
</div>

<? if (g2_import::is_initialized()): ?>
<div id="gAdminG2Import">
  <h1> <?= t("Import") ?> </h1>
  <div class="gSuccess">
    <?= t("Gallery version %version detected", array("version" => g2_import::version())) ?>
  </div>

  <?= t("You can perform an import on the <a href=\"%url\">maintenance page</a>",
        array("url" => url::site("admin/maintenance"))) ?>
</div>
<? endif ?>
