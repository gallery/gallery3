<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= HTML::clean($subject) ?> </title>
  </head>
  <body>
    <h2><?= HTML::clean($subject) ?></h2>
    <table>
      <tr>
        <td><?= t("Comment:") ?></td>
  <td><?= nl2br(HTML::purify($comment->text)) ?></td>
      </tr>
      <tr>
        <td><?= t("Author name:") ?></td>
        <td><?= HTML::clean($comment->author_name()) ?></td>
      </tr>
      <tr>
        <td><?= t("Author email:") ?></td>
        <td><?= HTML::clean($comment->author_email()) ?></td>
      </tr>
      <tr>
        <td><?= t("Author URL:") ?></td>
        <td><?= HTML::clean($comment->author_url()) ?></td>
      </tr>
      <tr>
        <td><?= t("Url:") ?></td>
        <td>
          <a href="<?= $comment->item->abs_url() ?>#comments">
            <?= $comment->item->abs_url() ?>#comments
          </a>
        </td>
      </tr>
    </table>
  </body>
</html>
