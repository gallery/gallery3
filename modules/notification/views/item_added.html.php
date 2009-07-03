<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= p::clean($subject) ?> </title>
  </head>
  <body>
    <h2><?= p::clean($subject) ?></h2>
    <table>
      <tr>
        <td><?= t("Title:") ?></td>
        <td><?= p::purifys($item->title) ?></td>
      </tr>
      <tr>
        <td><?= t("Url:") ?></td>
        <td>
          <a href="<?= $item->url(array(), true) ?>">
            <?= $item->url(array(), true) ?>
          </a>
        </td>
      </tr>
      <? if ($item->description): ?>
      <tr>
        <td><?= t("Description:") ?></td>
         <td><?= nl2br(p::purify($item->description)) ?></td>
      </tr>
      <? endif ?>
    </table>
  </body>
</html>
