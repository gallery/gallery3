<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= t("Password reset request") ?> </title>
  </head>
  <body>
    <h2><?= t("Password reset request") ?> </h2>
    <p>
      <?= t("Hello, %name,", array("name" => $user->full_name ? $user->full_name : $user->name)) ?>
    </p>
    <p>
  <?= t("We received a request to reset your password for <a href=\"%site_url\">%base_url</a>.  If you made this request, you can confirm it by <a href=\"%confirm_url\">clicking this link</a>.  If you didn't request this password reset, it's ok to ignore this mail.",
        array("site_url" => html::mark_clean(url::abs_site("/")),
              "base_url" => html::mark_clean(url::base(false)),
              "confirm_url" => $confirm_url)) ?>
    </p>
  </body>
</html>
