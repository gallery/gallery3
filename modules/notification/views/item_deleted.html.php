<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
<head>
  <title><?= $subject ?> </title>
</head>
<body>
  <h2><?= sprintf(t("%s: %s was deleted from %s"), $type, $item_title, $parent_title) ?></h2>
  <table>
    <tr>
    <td colspan="2"><?= sprintf(t("To view the changed album %s use the link below."), $parent_title) ?></td>
    </tr>
    <tr>
      <td><?= t("Url:") ?></td>
      <td><a href="<?= $url ?>"><?= $url ?></a></td>
    </tr>
  </table>
</body>
</html>
