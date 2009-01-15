<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <li>
    <?= t("Version: %version", array("version" => module::get_var("core", "version"))) ?>
  </li>
  <li>
    <?= t("Albums: %count", array("count" => $album_count)) ?>
  </li>
  <li>
    <?= t("Photos: %count", array("count" => $photo_count)) ?>
  </li>
</ul>
