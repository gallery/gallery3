<?php defined("SYSPATH") or die("No direct script access.") ?>
<p>
  <a href="<?= url::site("admin/themes/choose/$type/$theme_name?csrf=" . access::csrf_token()) ?>">
    <?= t("Activate <strong>{{theme_name}}</strong>", array("theme_name" => $info->name)) ?>
  </a>
</p>
<iframe src="<?= $url ?>" style="width: 900px; height: 450px"></iframe>
