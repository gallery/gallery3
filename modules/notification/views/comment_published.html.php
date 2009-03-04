<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= $subject ?> </title>
  </head>
  <body>
    <h2><?= $subject ?></h2>
    <table>
      <tr>
        <td><?= t("Comment:") ?></td>
        <td><?= $comment->text ?></td>
      </tr>
      <tr>
        <td><?= t("Author Name:") ?></td>
        <td><?= $comment->author_name() ?></td>
      </tr>
      <tr>
        <td><?= t("Author Email:") ?></td>
        <td><?= $comment->author_email() ?></td>
      </tr>
      <tr>
        <td><?= t("Author URL:") ?></td>
        <td><?= $comment->author_url() ?></td>
      </tr>
      <tr>
        <td><?= t("Url:") ?></td>
        <td><a href="<?= $comment->item()->url(array(), true) ?>#comments"><?= $comment->item()->url(array(), true) ?>#comments</a></td>
      </tr>
    </table>
  </body>
</html>
