<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= p::clean($subject) ?> </title>
  </head>
  <body>
    <h2><?= p::clean($subject) ?></h2>
    <table>
      <tr>
        <td><?= t("Comment:") ?></td>
        <td><?= p::clean($comment->text) ?></td>
      </tr>
      <tr>
        <td><?= t("Author Name:") ?></td>
        <td><?= p::clean($comment->author_name()) ?></td>
      </tr>
      <tr>
        <td><?= t("Author Email:") ?></td>
        <td><?= p::clean($comment->author_email()) ?></td>
      </tr>
      <tr>
        <td><?= t("Author URL:") ?></td>
        <td><?= p::clean($comment->author_url()) ?></td>
      </tr>
      <tr>
        <td><?= t("Url:") ?></td>
        <td>
          <a href="<?= $comment->item()->url(array(), true) ?>#comments">
            <?= $comment->item()->url(array(), true) ?>#comments
          </a>
        </td>
      </tr>
    </table>
  </body>
</html>
