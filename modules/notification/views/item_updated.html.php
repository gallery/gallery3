<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= p::clean($subject) ?> </title>
  </head>
  <body>
    <h2> <?= p::clean($subject) ?> </h2>
    <table>
      <tr>
        <? if ($item->original("title") != $item->title): ?>
        <td><?= t("New Title:") ?></td>
        <td><?= p::clean($item->title) ?></td>
        <? else: ?>
        <td><?= t("Title:") ?></td>
        <td><?= p::clean($item->title) ?></td>
        <? endif ?>
      </tr>
      <tr>
        <td><?= t("Url:") ?></td>
        <td><a href="<?= $item->url(array(), true) ?>"><?= $item->url(array(), true) ?></a></td>
      </tr>
      <? if ($item->original("description") != $item->description): ?>
      <tr>
        <td><?= t("New Description:") ?></td>
        <td><?= p::clean($item->description) ?></td>
      </tr>
      <? elseif (!empty($item->description)): ?>
      <tr>
        <td><?= t("Description:") ?></td>
        <td><?= p::clean($item->description) ?></td>
      </tr>
      <? endif ?>
    </table>
  </body>
</html>
