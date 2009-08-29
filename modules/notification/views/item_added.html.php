<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= SafeString::of($subject) ?> </title>
  </head>
  <body>
    <h2><?= SafeString::of($subject) ?></h2>
    <table>
      <tr>
        <td><?= t("Title:") ?></td>
        <td><?= SafeString::purify($item->title) ?></td>
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
         <td><?= nl2br(SafeString::purify($item->description)) ?></td>
      </tr>
      <? endif ?>
    </table>
  </body>
</html>
