<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
<head>
  <title><?= $subject ?> </title>
</head>
<body>
  <h2><?= sprintf(t("A new %s was added to %s"), $type, $parent_title); ?></h2>
  <table>
    <tr>
      <td><?= t("Title:") ?></td>
      <td><?= $item_title ?></td>
    </tr>
    <tr>
      <td><?= t("Url:") ?></td>
      <td><a href="<?= $url ?>"><?= $url ?></a></td>
    </tr>
    <? if (!empty($description)): ?>
    <tr>
      <td><?= t("Description:") ?></td>
      <td><?= $description ?></td>
    </tr>
    <? endif ?>
  </table>
</body>
</html>
