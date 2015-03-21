<?php defined("SYSPATH") or die("No direct script access.") ?>
<h1><?php echo  t("Preview of the %theme_name theme", array("theme_name" => $info->name)) ?></h1>
<p>
  <a href="<?php echo  url::site("admin/themes/choose/$type/$theme_name?csrf=$csrf") ?>">
    <?php echo  t("Activate <strong>%theme_name</strong>", array("theme_name" => $info->name)) ?>
  </a>
</p>
<iframe src="<?php echo  $url ?>" style="width: 900px; height: 450px"></iframe>
