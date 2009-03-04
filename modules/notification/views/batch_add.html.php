<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
<head>
  <title><?= $subject ?> </title>
</head>
<body>
  <h2><?= sprintf(t("New Photos, Movies or Albums have been added to %s."), $parent_title); ?></h2>
  <p><?= sprintf(t("Click on the link below to view the additions"), $parent_title); ?></p>
    <p><a href="<?= $url ?>"><?= t(here) ?></a><p>
</body>
</html>
