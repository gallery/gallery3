<?php defined("SYSPATH") or die("No direct script access.") ?>
<h1><?= t("Preview of the %theme_name theme", array("theme_name" => $info->name)) ?></h1>
<p>
  <a href="<?= url::site("admin/themes/choose/$type/$theme_name?csrf=$csrf") ?>">
    <?= t("Activate <strong>%theme_name</strong>", array("theme_name" => $info->name)) ?>
  </a>
</p>
<iframe src="<?= $url ?>" style="width: 900px; height: 450px"></iframe>
