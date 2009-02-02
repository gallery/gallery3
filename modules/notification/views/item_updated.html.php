<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
<head>
  <title><?= $subject ?> </title>
</head>
<body>
  <h2><?= sprintf(t("%s %s was updated"), ucfirst($type), $item_title); ?></h2>
  <table>
    <tr>
      <? if (!empty($new_title)): ?>
      <td><?= t("New Title:") ?></td>
      <td><?= $new_title ?></td>
      <? else: ?>
      <td><?= t("Title:") ?></td>
      <td><?= $item_title ?></td>
      <? endif ?>
    </tr>
    <tr>
      <td><?= t("Url:") ?></td>
      <td><a href="<?= $url ?>"><?= $url ?></a></td>
    </tr>
    <? if (!empty($new_description)): ?>
    <tr>
      <td><?= t("New Description:") ?></td>
      <td><?= $new_description ?></td>
    </tr>
       <? else: if (!empty($description)): ?>
    <tr>
      <td><?= t("Description:") ?></td>
      <td><?= $description ?></td>
    </tr>
    <? endif ?>
    <? endif ?>
  </table>
</body>
</html>
