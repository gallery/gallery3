<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= html::clean($subject) ?> </title>
  </head>
  <body>
    <h2><?= html::clean($subject) ?></h2>
    <table>
      <tr>
        <td colspan="2">
          <?= t("To view the changed album %title use the link below.",
              array("title" => html::purify($item->parent()->title))) ?>
        </td>
      </tr>
      <tr>
        <td><?= t("Url:") ?></td>
        <td>
          <a href="<?= $item->parent()->abs_url() ?>">
            <?= $item->parent()->abs_url() ?>
          </a>
        </td>
      </tr>
    </table>
  </body>
</html>
