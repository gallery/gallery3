<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
<head>
  <title><?= $subject ?> </title>
</head>
<body>
  <h2><?= sprintf(t("A new comment was added by %s"), $author); ?></h2>
  <table>
    <tr>
      <td><?= t("Comment:") ?></td>
      <td><?= $text ?></td>
    </tr>
    <tr>
      <td><?= t("Url:") ?></td>
      <td><a href="<?= $url ?>"><?= $url ?></a></td>
    </tr>
  </table>
</body>
</html>
