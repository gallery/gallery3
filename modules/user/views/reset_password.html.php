<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
<head>
  <title><?= $title ?> </title>
</head>
<body>
  <h2><?= t("Password Reset Request") ?> </h2>
  <p>
    <?= sprintf(t("A request to reset your password (user: %s) at %s."), $name, url::base(false, "http")) ?>
    <?= sprintf(t("To confirm this request please click on the link below")) ?><br />
    <a href="<?= $url ?>"><?= t("Reset Password") ?></a>
  </p>
</body>
</html>
