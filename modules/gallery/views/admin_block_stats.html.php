<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <li>
    <?php echo  t("Version: %version", array("version" => gallery::version_string())) ?>
  </li>
  <li>
    <?php echo  t("Albums: %count", array("count" => $album_count)) ?>
  </li>
  <li>
    <?php echo  t("Photos: %count", array("count" => $photo_count)) ?>
  </li>
</ul>
