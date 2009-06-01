<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= p::clean($subject) ?> </title>
  </head>
  <body>
    <h2> <?= p::clean($subject) ?> </h2>
    <table>
      <tr>
        <? if ($old->title != $new->title): ?>
        <td><?= t("New Title:") ?></td>
        <td><?= p::clean($new->title) ?></td>
        <? else: ?>
        <td><?= t("Title:") ?></td>
        <td><?= p::clean($new->title) ?></td>
        <? endif ?>
      </tr>
      <tr>
        <td><?= t("Url:") ?></td>
        <td><a href="<?= $new->url(array(), true) ?>"><?= $new->url(array(), true) ?></a></td>
      </tr>
      <? if ($old->description != $new->description): ?>
      <tr>
        <td><?= t("New Description:") ?></td>
        <td><?= p::clean($new->description) ?></td>
      </tr>
      <? elseif (!empty($new->description)): ?>
      <tr>
        <td><?= t("Description:") ?></td>
        <td><?= p::clean($new->description) ?></td>
      </tr>
      <? endif ?>
    </table>
  </body>
</html>
