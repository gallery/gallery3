<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= html::clean($subject) ?> </title>
  </head>
  <body>
    <h2> <?= html::clean($subject) ?> </h2>
    <table>
      <tr>
        <?php if ($original->title != $item->title): ?>
        <td><?= t("New title:") ?></td>
        <td><?= html::clean($item->title) ?></td>
        <?php else: ?>
        <td><?= t("Title:") ?></td>
        <td><?= html::clean($item->title) ?></td>
        <?php endif ?>
      </tr>
      <tr>
        <td><?= t("Url:") ?></td>
        <td><a href="<?= $item->abs_url() ?>"><?= $item->abs_url() ?></a></td>
      </tr>
      <?php if ($original->description != $item->description): ?>
      <tr>
        <td><?= t("New description:") ?></td>
        <td><?= html::clean($item->description) ?></td>
      </tr>
      <?php elseif (!empty($item->description)): ?>
      <tr>
        <td><?= t("Description:") ?></td>
        <td><?= html::clean($item->description) ?></td>
      </tr>
      <?php endif ?>
    </table>
  </body>
</html>
