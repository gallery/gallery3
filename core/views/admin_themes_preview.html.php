<h1> <?= t("Theme Preview: {{theme_name}}", array("theme_name" => $info->name)) ?> </h1>
<iframe src="<?= $url ?>" style="width: 600px; height: 500px"></iframe>
<p>
  <a href="<?= url::site("admin/themes/choose/$type/$theme_name?csrf=" . access::csrf_token()) ?>">
    <?= t("Activate <b>{{theme_name}}</b>", array("theme_name" => $info->name)) ?>
  </a>
</p>
