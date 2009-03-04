<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= $subject ?> </title>
  </head>
  <body>
    <h2><?= $subject ?></h2>
    <table>
      <tr>
        <td colspan="2">
          <?= t("To view the changed album %title use the link below.",
              array("title" => $item->parent()->title)) ?>
        </td>
      </tr>
      <tr>
        <td><?= t("Url:") ?></td>
        <td><a href="<?= $item->parent()->url(array(), true) ?>"><?= $item->parent()->url(array(), true) ?></a></td>
      </tr>
    </table>
  </body>
</html>
