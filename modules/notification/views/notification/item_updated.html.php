<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= html::clean($subject) ?> </title>
  </head>
  <body>
    <h2> <?= html::clean($subject) ?> </h2>
    <table>
      <tr>
        <? if ($original->title != $item->title): ?>
        <td><?= t("New title:") ?></td>
        <td><?= html::clean($item->title) ?></td>
        <? else: ?>
        <td><?= t("Title:") ?></td>
        <td><?= html::clean($item->title) ?></td>
        <? endif ?>
      </tr>
      <tr>
        <td><?= t("Url:") ?></td>
        <td><a href="<?= $item->abs_url() ?>"><?= $item->abs_url() ?></a></td>
      </tr>
      <? if ($original->description != $item->description): ?>
      <tr>
        <td><?= t("New description:") ?></td>
        <td><?= html::clean($item->description) ?></td>
      </tr>
      <? elseif (!empty($item->description)): ?>
      <tr>
        <td><?= t("Description:") ?></td>
        <td><?= html::clean($item->description) ?></td>
      </tr>
      <? endif ?>
    </table>
  </body>
</html>
